<?php
namespace SLLH\ComposerLint;

class NoPrependAutoLoaderLintRule implements LintRule {
    
    /*
     * {@inheritdoc }
     */
    public function __construct($config) {
    }

    /*
     * {@inheritdoc }
     */
    public function validate($manifest, $errors) {
        if (isset($manifest['config']['prepend-autoloader']) &&
            false !== $manifest['config']['prepend-autoloader']) {
            array_push($errors, 'prepend-autoloader must be false.');
        }
        return $errors;
    }
}

