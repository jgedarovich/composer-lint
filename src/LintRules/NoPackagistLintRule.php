<?php
namespace SLLH\ComposerLint;

class NoPackagistLintRule implements LintRule {
    
    /*
     * {@inheritdoc }
     */
    public function __construct(Array $config) {
    }

    /*
     * {@inheritdoc }
     */
    public function validate($manifest, $errors) {
        if ( isset($manifest['config']['repositories']['packagist.org'])) {
            array_push($errors, 'the packagist repository must be turned off with the config packagist:false ');
        }
        return $errors;
    }
}

