<?php

namespace Phutility;

class Func {
	const x = 'wildcard';
	
	public static function reflect($func) {
		$r = new \ReflectionObject($func);
		return $r->getMethod('__invoke');
	}
	
	public static function arity($func) {
	    return self::reflect($func)->getNumberOfParameters();
	}
		
	public static function getParameters($func) {
		return self::reflect($func)->getParameters();
	}
	
	public static function rest($func, $transform = false) {		
		if(!$func instanceof \Closure) {
			throw new \Exception('1st param must be a closure');
		}
		
		if(!$transform) { 
			$transform = function($i) { return $i; };
		}
		
		$num = self::arity($func) - 1;
		
		return function() use($num, $func, $transform) {
			$args = func_get_args();

			if(count($args) < $num) { 
				throw new \Exception("Not enough arguments. Expected $num got " . count($args));
			}
						
			return call_user_func_array(
				$func,
				array_merge(
					array_slice($args, 0, $num),
					array($transform(array_slice($args, $num)))));
		};
	}

	public static function args($func) {
		return function() use($func) {
			return $func(func_get_args());
		};
	}
	
	public static function options($func) {		
		return Func::rest($func, function($i) { return Appos::create($i); });
	}
		
	public static function keyword($func) {
		$kwlist = array();
		foreach(self::getParameters($func) as $arg) {
			$kwlist[$arg->name] = false;
		}
		
		return self::options(function($args) use($func, $kwlist) {
			foreach($kwlist as $key => $val) {
				if(!isset($args[$key])) {
					throw new \Exception("Expect $key but was not found in " . json_encode(array_keys($args)));
				}
				$kwlist[$key] = $args[$key];
			}
			return call_user_func_array($func, array_values($kwlist));
		});
	}
	
	public static function matchType() {
		$funcs = array();
		
		$isMatchBuilder = function($arity, $func, $types) {
			return Func::args(function($args) use($arity, $func, $types) {
				if(count($args) !== $arity) {
					return null;
				}

				foreach($args as $a => $arg) {
					if(!is_object($arg)) {
						var_dump($arg);
						throw new \Exception("All arguments should be objects");
					}
					if(!$arg instanceof $types[$a]) {
						return $continue;
					}
				}
				
				return call_user_func_array($func, $args);
			});
		};
		
		$types = array();		
		foreach(func_get_args() as $arg) {
			if(is_string($arg)) { 
				$types[] = $arg;
			}
			
			if($arg instanceof \Closure) {
				$funcs[] = $isMatchBuilder(Func::arity($arg), $arg, $types);
				$types = array();
			}
		}
			
		if(!empty($type)) {
			throw new \Exception("Should end w/ closure..");
		}

		return Func::args(function($args) use($funcs) { 
			foreach($funcs as $func) {
				$result = call_user_func_array($func, $args);
				if(is_null($result)) {
					continue;
				} else {
					return $result;
				}
			}
			throw new \Exception("No match found for args passed");
		});
	}
	
	public static function match() {
		return function() {};
	}
}
