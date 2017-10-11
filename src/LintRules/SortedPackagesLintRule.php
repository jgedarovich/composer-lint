<?php
namespace SLLH\ComposerLint;

class SortedPackagesLintRule implements LintRule {
    
    /*
     * {@inheritdoc }
     */
    public function __construct($config) {
    }

    /*
     * {@inheritdoc }
     */
    public function validate($manifest, $errors) {

        //TODO: dry this up since it's used in another lint?
        $linksSections = array('require', 'require-dev', 'conflict', 'replace', 'provide', 'suggest');

        foreach ($linksSections as $linksSection) {
            if (array_key_exists($linksSection, $manifest) && !$this->packagesAreSorted($manifest[$linksSection])) {
                array_push($errors, 'Links under '.$linksSection.' section are not sorted.');
            }
        }

        return $errors;
    }


    private function packagesAreSorted(array $packages)
    {
        $names = array_keys($packages);

        $hasPHP = in_array('php', $names);
        $extNames = array_filter($names, function ($name) {
            return 'ext-' === substr($name, 0, 4) && !strstr($name, '/');
        });
        sort($extNames);
        $vendorName = array_filter($names, function ($name) {
            return 'ext-' !== substr($name, 0, 4) && 'php' !== $name;
        });
        sort($vendorName);

        $sortedNames = array_merge(
            $hasPHP ? array('php') : array(),
            $extNames,
            $vendorName
        );

        return $sortedNames === $names;
    }

}

