<?php
namespace Phunderscore;

class String {
    private $value; 

    public function __construct($value) {
        $this->value = (string)$value;
    }

    public function __get($method) {
        if(is_callable(array($this, $method))) {
            return $this->$method();
        } else {
            throw new Exception("No $method on String");
        }
    }
    
    public function toUpper() {
        return new String(strtoupper($this->value));
    }

    public function reverse() {
        return new String(strrev($this->value));
    }

    public function __toString() {
        return "{$this->value}";
    }
}