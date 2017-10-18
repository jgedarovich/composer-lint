<?php
namespace SLLH\ComposerLint;

class ClassmapAuthoritativeLintRule implements LintRule {
    
    /*
     * {@inheritdoc }
     */
    public function __construct(Array $config) {
    }

    /*
     * {@inheritdoc }
     */
    public function validate($manifest, $errors) {
        if ( isset($manifest['config']['classmap-authoritative']) &&
            true !== $manifest['config']['classmap-authoritative']) {
            array_push($errors, 'classmap-authoritative must be true.');
        }
        return $errors;
    }
}

