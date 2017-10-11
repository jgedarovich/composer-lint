<?php
namespace SLLH\ComposerLint;

class NoUseIncludePathLintRule implements LintRule {
    
    /*
     * {@inheritdoc }
     */
    public function __construct($config) {
    }

    /*
     * {@inheritdoc }
     */
    public function validate($manifest, $errors) {
        if ( isset($manifest['config']['use-include-path']) &&
            false !== $manifest['config']['use-include-path']) {
            array_push($errors, 'use-include-path must be false.');
        }

        return $errors;
    }
}

