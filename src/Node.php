<?php
namespace Phutility;

class Node {
	protected $_type;
	protected $_vals;
	public function __construct($type, $vals) {
		$this->_type = $type;
		$this->_vals = $vals;
	}
	
	public function __get($name) {
		$valTypes = array();
		foreach($this->_vals as $val) {
			if($val->isType($name)) {
				return $val;
			}
			$valTypes[] = $val->getType();
		}
		throw new \Exception("{$name} was not found. Contains: [" . implode(', ', $valTypes) . ']');
	}
	
	public function first() {
		return reset($this->_vals);
	}
	
	public function last() {
		return end($this->_vals);
	}
	
	public function nth($n) {
		if(isset($this->_vals[$n])) {
			return $this->_vals[$n];
		} else {
			throw new \Exception("index {$n} does not exist! Only contains indexes[0.." . (count($this->_vals) - 1). ']');
		}
	}
	
	public function find() {
		$args = func_get_args();
		if(current($args) instanceof \Closure) {
			$func = current($args);
		} else if(count($args) == 2) {
			list($type, $firstval) = $args;
			$func = function($i) use($type, $firstval) { 
				return $i->isType($type) && $i->first() == $firstval;
			};
		} else if(count($args) == 1) {
			list($scalar) = $args;
			$func = function($i) use($scalar) { 
				return $i == $scalar;
			};
		}
		
		foreach($this->_vals as $val) {
			if($func($val)) {
				return $val;
			}
		}
		throw new \Exception('No elements found which match closure');
	}

	public function has($type) {
		foreach($this->_vals as $val) {
			if($val instanceof Node && $val->isType($type)) {
				return $val;
			}
			if(is_scalar($val) && $val == $type) { 
				return $val;
			}
		}
		return false;
	}
		
	public function getType() {
		return $this->_type;
	}
	
	public function isType($type) {
		return $this->_type == $type;
	}
	
	public function add($node) {
		$this->_vals[] = $node;
		return $this;
	}
}