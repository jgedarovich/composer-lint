<?php
namespace SLLH\ComposerLint;

class PhpLintRule implements LintRule {
    
    /*
     * {@inheritdoc }
     */
    public function __construct($config) {
    }

    /*
     * {@inheritdoc }
     */
    public function validate($manifest, $errors) {
        if ((array_key_exists('require-dev', $manifest) || array_key_exists('require', $manifest))) {
            $isOnRequireDev = array_key_exists('require-dev', $manifest) && array_key_exists('php', $manifest['require-dev']);
            $isOnRequire = array_key_exists('require', $manifest) && array_key_exists('php', $manifest['require']);

            if ($isOnRequireDev) {
                array_push($errors, 'PHP requirement should be in the require section, not in the require-dev section.');
            } elseif (!$isOnRequire) {
                array_push($errors, 'You must specify the PHP requirement.');
            }
        }
        return $errors;
    }
}

