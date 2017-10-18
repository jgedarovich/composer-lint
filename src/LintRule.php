<?php

namespace SLLH\ComposerLint;

interface LintRule {
    /*
     * @param Array $config - config parsed in from .composerlint / composerlintignore / config stanza of composer.json
     * @param Array $errors - an array to add erros to - this will be what gets returned
     * @return void
     */
    public function __construct(Array $config);

    /*
     * @param Array $manifest - composer.json in array format
     * @param Array $errors - an array to add erros to - this will be what gets returned
     * @return Array $errors - the $error array that was given - with any violations added to it
     */
    public function validate($manifest, $errors);
}
