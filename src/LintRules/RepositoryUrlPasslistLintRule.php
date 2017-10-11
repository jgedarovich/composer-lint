<?php
namespace SLLH\ComposerLint;

class RepositoryUrlPasslistLintRule implements LintRule {
    
    /*
     * {@inheritdoc }
     */
    public function __construct($config) {
    }

    /*
     * {@inheritdoc }
     */
    public function validate($manifest, $errors) {

        if ( array_key_exists("repository-passlist", $this->config) &&
            is_array($this->config['repository-passlist']) ){
            //TODO: check each repository url for passlist
            array_push($errors, 'the packagist repository must be turned off with the config packagist:false ');
        }


        return $errors;
    }
}

