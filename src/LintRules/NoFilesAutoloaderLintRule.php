<?php
namespace SLLH\ComposerLint;

/*
 * @author jimbo gedarovich <james.gedarovich@gmail.com>
 */
class NoFilesAutoloaderLintRule implements LintRule {
    /*
     * @var array
     */
    private $config;
    /*
     * @var array
     */
    private $files_safelist;
    /*
     * @var string
     */
    private $custom_comment;

    private $package_queue = [];
    private $results_array= [];
    private $files_autoloader_files = [];

    /*
     * @var \JMS\Composer\Graph\DependencyGraph
     */
    private $graph;
    /*
     * @var \JMS\Composer\Graph\DependencyEdge
     */
    private $root;
    
    /*
     * @var string
     */
    private $root_package_name;

    /**
     * Take the graph generated from the DependencyAnalyzer, and lint each node of that gdata structure
     * only report errors at the root or top level, make folks aware of errors in transative dependencies
     * through tieing them to the top level project they are associated with
     *  
     *               ROOT
     *           /           \
     *
     *   top level 1         top level 2         <-- only report errors at this level really
     *       /                   \
     *   transative 1        transative 2        <-- these came along because of top level deps
     *
     * @param array     $config - for errors etc.
     */
    function __construct( Array $config = [], $dir= "." ) {
        $this->config = $config;
        $this->files_safelist = isset($config['ignore']) && is_array($config['ignore']) ? $config['ignore'] :[];
        $this->custom_comment = isset($config['custom-comment']) && is_string($config['custom-comment']) ? $config['custom-comment'] : "";
        $analyzer = new \JMS\Composer\DependencyAnalyzer();

        /*
         * the next two conditionals  and AnablyzeComposerData function call
         * are  essentially the DependencyAnalyzers 'analyze' function 
         * but that's not testable due to 'realpath' usage..
         */
        if ( ! is_dir($dir)) {
            throw new \InvalidArgumentException(sprintf('The directory "%s" does not exist.', $dir));
        }

        if ( ! is_file($dir.'/composer.json') || ! is_file($dir.'/composer.lock')) {
            throw new \InvalidArgumentException(sprintf('The directory "%s" does not contain a composer.json and or a composer.lock - they are both required for this lint to work.', $dir));
        }
        
        $this->graph = $analyzer->analyzeComposerData(
            file_get_contents($dir.'/composer.json'),
            file_get_contents($dir.'/composer.lock'),
            $dir
        );

        $this->root = $this->graph->getRootPackage();
        $this->root_package_name = $this->root->getName();
    }

    /*
     * {@inheritdoc }
     */
    public function validate($manifest, $errors) {
        $this->checkProject();
        //eval(\Psy\sh());
        return $this->results_array;
    }

    /**
     *   Add any files autoloader issues that were collected to the results array
     *   this is an additional message that will show exactly which files
     */
    private function reportOnFilesAutoloaderFiles(): void {
        $files_error_string = "";
        foreach ( $this->files_autoloader_files as $top_level_package_name => $files_array ) {
            $files_error_string = $files_error_string . "Due to the inclusion of ". $top_level_package_name. " the following file(s) would be loaded on every single request\n";
            foreach( $files_array  as $file_path_string ) {
                $files_error_string = $files_error_string . "     ".$file_path_string."\n";
            }

            if ( $this->custom_comment !== "" ) {
                $files_error_string .= $this->custom_comment;
            }
        }
        if ( strlen($files_error_string) > 0 ) {
            $this->results_array[] = $files_error_string;
        }
    }

    /**
     * check for the existence of forbidden files autoloader type
     * add to the results array if fonud
     *
     * @param Array @package_data 
     * @param Array @top_level_package
    */
    private function reportIfUsingFilesAutoloader($package_data, $top_level_packages = []): void {
        $package_data_copy = $package_data;

        if ( array_key_exists('autoload', $package_data) && array_key_exists('files', $package_data['autoload'])) {

            if ( count($top_level_packages) > 0 ) {

                //report transative dependency errors at the top level
                foreach ( $top_level_packages as $package_name) {

                    //make a copy because this is destructive
                    $package_data_copy = $package_data;
                    $previously_safelisted = [];
                    $all_files_safelisted = array_reduce(
                        $package_data_copy['autoload']['files'],
                        function($acc, $cur) use (&$previously_safelisted, &$package_data_copy) {
                            $full_path = 'vendor/'.$package_data_copy['name'].'/'.$cur;
                            if (in_array($full_path, $this->files_safelist)) {
                                $previously_safelisted[] = $full_path;
                                $key = array_search($full_path, $package_data_copy['autoload']['files']);
                                unset($package_data_copy['autoload']['files'][$key]);
                                return true;
                            } else {
                                return $acc;
                            }
                        },
                        false
                    );
                    
                    if ( !$all_files_safelisted ) {
                        $previously = count($previously_safelisted)>0 ? "\n\nsome files were already safelisted - but not all, perhaps an update brough in more dependencies. for reference these files were already safelisted".implode("\n-",$previously_safelisted) : "";

                        if ( $package_name == $package_data['name'] ) {
                            $this->results_array[] = 
                                "package ".$package_name." is using the forbidden files autolader type".$previously;
                        } else {
                            $this->results_array[] = 
                                "package ".$package_name." requires ".$package_data['name']." as a transative dependency and  that package is using the forbidden files autolader type".$previously;
                        }

                        foreach( $package_data_copy['autoload']['files'] as $file ) {
                            $this->files_autoloader_files[$package_name][] = 'vendor/'.$package_data['name'].'/'.$file;
                        }
                    }
                }
            } else {
                $this->results_array[] = "package ".$package_data['name']." is using the forbidden files autolader type";
            }
        }
    }

    /**
     * check for the existence of an autoloader, except for the root package
     * if found add it to the results array
     *
     * @param Array @package_data 
     * @param Array @top_level_package
    */
    private function reportIfMissingAutoloader($package_data, $top_level_packages = []): void {

        if ( !array_key_exists('autoload', $package_data)) {

            if ( count($top_level_packages) > 0 ) {

                //report transative dependency errors at the top level
                foreach ( $top_level_packages as $package_name ) {
                    //the only time when this wolud be missing is if the json and lock were out of sync which is checked by something else
                    if ( !array_key_exists('name', $package_data) ) {
                        continue;
                    }
                    //add it to missing_autoloader with traceback to top level package
                    $this->results_array[] = 
                        "package ".$package_name." requires ".$package_data['name']." as a transative dependency and that package is missing an autolader definition";
                }
            } else {
                $this->results_array[] = "package ".$package_data['name']." is missing autolader definition";
            }
        }
    }

    /**
     *  Do a BFS down the dependency graph starting from root,
     *  report all found errors
     */
    private function checkProject(): void {
        $this->package_queue = [$this->root];
        $this->reportIfUsingFilesAutoloader($this->root->getData());
        $seen_it = [];

        while ( count($this->package_queue) > 0 ) {
            $current_package = array_pop($this->package_queue);
            $out_edges = $current_package->getOutEdges();

            foreach ( $out_edges as $edge ) {

                $package = $edge->getDestPackage();
                $package_data = $package->getData();
                $package_name = $package->getName();

                //allow dev dependencies to use files autoloader, and dont look at php
                if ( $edge->isDevDependency() || $package_name === "php") {
                    continue; 
                } else {

                    if ( !in_array( $package_name, $seen_it) ){

                        //enqueue
                        array_unshift($this->package_queue, $package);
                        array_push($seen_it, $package_name);
                    
                        $top_level_packages_for_edge = $this->getTopLevelPackagesForEdge([$edge]);
                        $this->reportifMissingAutoLoader($package_data, $top_level_packages_for_edge);
                        $this->reportIfUsingFilesAutoloader($package_data, $top_level_packages_for_edge);
                    }
                }

            }
        }
        $this->reportOnFilesAutoloaderFiles();
   }
  
    /*
    * Sort of DFS back up the dependency graph, in order to find the parent package,
    * which will be either the root, or one of this project's requires. 
    * never a transative dependency.
    
    * TODO: replace notstring with whatever the edge class name is
    * @param $edge_array - array of \JMS\Composer\Graph\DependencyEdge
    * @param $discovered - array of \JMS\Composer\Graph\DependencyEdge
    * @returns Array
    */
    private function getTopLevelPackagesForEdge($edge_array, $discovered = []): array {

        return array_reduce(
            $edge_array,
            function ($accumulator, $edge) use ($discovered) {

                $source_package = $edge->getSourcePackage();    
                $source_package_name = $source_package->getName();
                $source_package_in_edges = $source_package->getInEdges();
                $dest_package = $edge->getDestPackage();
                $dest_package_name = $dest_package->getName();

                if ($source_package_name==="php") {
                    return [];
                }

                if (
                    empty($source_package_in_edges) 
                    || $this->graph->isRootPackageName($source_package_name) 
                ) {
                    //the given edge is the root or 
                    //it's a top level package, IE it's included as a dependency of the root project
                    return [$dest_package_name];
                }

                if ( in_array($source_package_name, $discovered) ) {
                    return [];
                } else {
                    array_push($discovered, $source_package_name);
                    return array_unique(
                        array_merge(
                            $accumulator,
                            $this->getTopLevelPackagesForEdge(
                                $source_package_in_edges,
                                $discovered
                            )
                        )
                    );
                }
            },
            []
        );
    }
}


