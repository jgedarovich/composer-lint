<?php
namespace SLLH\ComposerLint;
use ArrayObject;

class ArrayOfLintRules extends ArrayObject {

    public function __construct($array = array()) {
        parent::__construct($array);
        $this->setIteratorClass('ArrayIterator');
    }

    public function offsetSet($key, $val) {
        if ($val instanceof LintRule) {
            return parent::offsetSet($key, $val);
        }
        throw new InvalidArgumentException('Value must be a LintRule');
    }
}
