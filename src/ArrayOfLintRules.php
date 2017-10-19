<?php
namespace SLLH\ComposerLint;
use ArrayObject;

class ArrayOfLintRules extends ArrayObject {

    /*
     * @var arary
     */
    private $all_lint_rules;

    public function __construct($array = array()) {
        parent::__construct($array);

        $this->all_lint_rules = array_map(function($filename) {
            return basename($filename, '.php');
        },glob(dirname(__FILE__).'/LintRules/*.php'));

        $this->setIteratorClass('ArrayIterator');
    }

    public function offsetSet($key, $val) {
        if ($val instanceof LintRule) {
            return parent::offsetSet($key, $val);
        }
        throw new InvalidArgumentException('Value must be a LintRule');
    }

    public function addLint($lint_class_name, array $lint_class_config) {

        if ( in_array($lint_class_name, $this->all_lint_rules) ) {
            $namespaced_lint_class_name="\\".__NAMESPACE__."\\".$lint_class_name;
            $this->append(new $namespaced_lint_class_name($lint_class_config));
        } else {
            throw new \InvalidArgumentException('can only add classes of type LintRule');
        }
    }
}
