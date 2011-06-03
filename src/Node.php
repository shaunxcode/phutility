<?php
namespace Phutility;

use Phutility\Invokable as I;

class Node {
	protected $_type;
	protected $_vals;
	public function __construct($type, $vals = array()) {
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
	
	public function prior() {
		return array_slice($this->_vals, 0, -1);
	}
	
	public function rest() {
		return array_slice($this->_vals, 1);
	}
	
	public function nth($n) {
		if(isset($this->_vals[$n])) {
			return $this->_vals[$n];
		} else {
			throw new \Exception("index {$n} does not exist! Only contains indexes[0.." . (count($this->_vals) - 1). ']');
		}
	}
	
	public function each($func) {
		foreach($this->_vals as &$val) {
			$result = $func($val);
			if(!is_null($result)) {
				$val = $result;
			}
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

	public function all($func = false) {
		return $func ? array_filter($this->_vals, $func) : $this->_vals;
	}
	
	public function allOfType($type) {
		return $this->all(I::isType($type));
	}
	
	public function is($val) {
		return $this->_vals == $val;
	}
	
	public function filter($func) {
		return array_filter($this->_vals, $func);
	}
	
	public function has($type) {
		foreach($this->_vals as $val) {
			if($type instanceof \Closure) {
				if($type($val)) { 
					return true;
				} else {
					continue;
				}
			}
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
	
	public function add() {
		$vals = func_get_args();
		if(count($vals) == 1 && is_array(current($vals))) {
			$vals = current($vals);
		}
		
		foreach($vals as $node) {
			$this->_vals[] = $node;
		}
		return $this;
	}
	
	public function toArray() {
		$array = array();
		foreach($this->_vals as $val) { 
			if($val instanceof Node) {
				$array[] = array($val->getType() => $val->toArray());
			} else { 
				$array[] = is_scalar($val) ? $val : (array)$val;
			}
		}

		return $array;
	}
}