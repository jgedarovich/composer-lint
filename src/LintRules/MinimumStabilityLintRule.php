<?php
namespace SLLH\ComposerLint;

class MinimumStabilityLintRule implements LintRule {
    
    /*
     * {@inheritdoc }
     */
    public function __construct(Array $config) {
    }

    /*
     * {@inheritdoc }
     */
    public function validate($manifest, $errors) {

        if (array_key_exists('minimum-stability', $manifest) &&
            array_key_exists('type', $manifest) && 'project' !== $manifest['type']) {
            array_push($errors, 'The minimum-stability should be only used for project packages.');
        }

        return $errors;
    }
}

