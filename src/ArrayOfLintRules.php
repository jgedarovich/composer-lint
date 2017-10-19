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

            try {
                if ( !class_exists($namespaced_lint_class_name) ) {
                    require dirname(__FILE__).'/LintRules/'.$lint_class_name.".php";
                }
                $this->append(new $namespaced_lint_class_name($lint_class_config));
            } catch(Exception $e) {
                throw new \InvalidArgumentException("Unable to instantiate lint rule class '$lint_class_name', known lint class names are: ".implode(",", $this->all_lint_rules). " error message: " .$e->getMessage());
            }
        } else {
            throw new \InvalidArgumentException("Unknown lint rule class '$lint_class_name', known lint class names: ".implode(",", $this->all_lint_rules));
        }
    }
}
