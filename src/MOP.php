<?php
namespace Phutility\MOP;

class Registry {
	private static $classes = array(); 
	
	public static function addClass($name, $definition) {
		self::$classes[$name] = $definition;
		return $definition;
	}
	
	public static function getClass($name) {
		if(isset(self::$classes[$name])) { 
			return self::$classes[$name];
		} else {
			throw new \Exception("MOP Class not defined");
		}
	}
}

class MetaArg {
	private $type;
	private $info = array();
	
	public function __construct($type) {
		$this->type = $type;
	}
	
	public function __call($key, $val) {
		$this->info[$key] = array_shift($val);
		return $this;
	}
	
	public function isType($type) {
		return $this->type == $type;
	}
	
	public function get($key) {
		if(isset($this->info[$key])) {
			return $this->info[$key];
		} else {
			throw new \Exception("$key does not exist for {$this->type}");
		}
	}
}

function MetaArg($type) {
	return new MetaArg($type);
}

function isMetaArg($val, $type) {
	return $val instanceof MetaArg && $val->isType($type);
}

function defclass() {
	$args = func_get_args();
	$name = array_shift($args);
	$slots = array();
	$meta = array();
	
	foreach($args as $arg) {
		if(isMetaArg($arg, 'Slot')) { 
			$slots[] = $arg;
		}
		
		if(isMetaArg($arg, 'Extend')) {
			$meta['extend'] = $arg;
		}
	}

	return Registry::addClass(
		$name, 
		MetaArg('Class')
			->name($name)
			->meta($meta)
			->slots($slots));
}

function getclass($name) {
	return Registry::getClass($name);
}

function extend() {
	return MetaArg('Extend')->classes(func_get_args());
}


function slot($name) {
	return MetaArg('Slot')->name($name);
}

function before() {
	return MetaArg('Before');
}

function after() {
	return MetaArg('After');
}

function arity() {
	return MetaArg('Arity')->classes(func_get_args());
}

function defmethod() {
	
}