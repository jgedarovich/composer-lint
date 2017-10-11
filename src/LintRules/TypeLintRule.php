<?php
namespace SLLH\ComposerLint;

class TypeLintRule implements LintRule {
    
    /*
     * {@inheritdoc }
     */
    public function __construct($config) {
    }

    /*
     * {@inheritdoc }
     */
    public function validate($manifest, $errors) {

        if (!array_key_exists('type', $manifest)) {
            array_push($errors, 'The package type is not specified.');
        }

        return $errors;
    }
}

