<?php

namespace Phutility;

class Func {
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

	public static function arity($func) {
	    $r = new \ReflectionObject($func);
	    return $r->getMethod('__invoke')->getNumberOfParameters();
	}
	
	public static function options($func) {		
		return Func::rest($func, function($i) { return Appos::create($i); });
	}
}
