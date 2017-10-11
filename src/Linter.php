<?php

namespace SLLH\ComposerLint;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 * @author James Gedarovich <james.gedarovich@gmail.com>
 */
final class Linter
{
    /**
     * @var ArrayOfLintRules
     */
    private $lint_rules;

    /**
     * @param array $config
     */
    public function __construct(ArrayOfLintRules $lint_rules)
    {
        $this->lint_rules = $lint_rules;
    }

    /**
     * @param array $manifest composer.json file manifest
     *
     * @return string[]
     */
    public function validate($manifest)
    {
        $errors = array();
        /*
         * TODO:
         *  - vendor is git ignored
         */
        foreach ( $this->lint_rules as $lint_rule ) {
            $errors = $lint_rule->validate($manifest, $errors);
        }

        return $errors;
    }
}
