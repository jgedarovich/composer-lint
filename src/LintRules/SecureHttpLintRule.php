<?php
namespace SLLH\ComposerLint;

class SecureHttpLintRule implements LintRule {
    
    /*
     * {@inheritdoc }
     */
    public function __construct($config) {
    }

    /*
     * {@inheritdoc }
     */
    public function validate($manifest, $errors) {
        if ( isset($manifest['config']['secure-http']) &&
            true !== $manifest['config']['secure-http']) {
            array_push($errors, 'secure-http must be true.');
        }
        return $errors;
    }
}

