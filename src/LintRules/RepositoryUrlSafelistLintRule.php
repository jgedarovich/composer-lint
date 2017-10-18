<?php
namespace SLLH\ComposerLint;

class RepositoryUrlSafelistLintRule implements LintRule {

    /*
     * @var array
     */
    private $config;

    /*
     * {@inheritdoc }
     */
    public function __construct(Array $config = [] ) {
        $this->config = $config;
    }

    /*
     * {@inheritdoc }
     */
    public function validate($manifest, $errors) {

        $repositories = isset($manifest['config']['repositories']) && is_array($manifest['config']['repositories']) ? $manifest['config']['repositories'] : [];
        
        //collect list of repositories from manifest
        $repository_urls = array_reduce(
            $repositories,
            function($acc, $repository): array {
                //todo is repository an array ? check that url is a key?
                $acc[] = $repository['url'];
                return $acc;
            },
            []
        );

        if ( 
            array_key_exists("repository-safelist", $this->config) &&
            is_array($this->config['repository-safelist']) 
        ){
            foreach ( $repository_urls as $url ) {
                if ( !in_array($url, $this->config['repository-safelist']) ) {
                    $errors[] = $url . ' is not in the repository safelist check .composerlint file for a list of allowed repository urls.';
                }
            }
        } else {
            $errors[] = "RepositoryUrlSafelist lint rule requires a configured list of repositories";
        }

        return $errors;
    }
}

