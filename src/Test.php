<?php
namespace Phutility;

class Test {
	public static $passCount = 0;
	public static $failCount = 0;
	public static $colors = array(
		'green' => "[0;32m",
		'red' => "[0;31m", 
		'white' => "[1;37m",
		'normal' => "[0m");
		
	public static function text($color, $text = '') {
		return chr(27) . self::$colors[$color] . $text;
	}
	
	private static function outputResult($description, $val, $isVal, $pass = false) {
		if($pass) { 
			self::$passCount++;
		} else {
			self::$failCount++;
		}
		
		echo self::text('white', $description . ' [' 
		   . self::text($pass ? 'green' : 'red', $pass ? 'PASS' : 'FAIL [expected: ' 
				. json_encode(is_callable(array($isVal, 'toArray')) ? $isVal->toArray() : $isVal) 
				. ' | got: ' 
				. json_encode(is_callable(array($val, 'toArray')) ? $val->toArray() : $val)  
				. ']'))
		   . self::text('white', ']') 
		   . self::text('normal', "\n");
			
		return $pass;		
	}
	
	public static function assert($description, $val, $isVal = true) {
		return self::outputResult($description, $val, $isVal, $val == $isVal);
	}
	
	public static function throwsException($description, $func, $exception = '\Exception') {
		$result = false;
		try { 
			$func();
		} catch (\Exception $e) {
			$result = true;
		}
		return self::outputResult($description, "[closure]", $exception, $result); 
		
	}
	
	public static function totals() {
		echo self::text('green', 'Total Passed: ' . self::$passCount) . "\n" 
		   . self::text('red', 'Total Failed: ' . self::$failCount) 
		   . self::text('normal', "\n"); 
	}
	
	public static function runAll() {		
		$start = microtime();
		foreach(glob('*Test.php') as $file) {
			echo self::text('white', "Test $file\n\n"); 
			$substart = microtime();
			require_once $file;
			$subend = microtime();
			echo self::text('white', 'Test Time: ' . ($subend - $substart) . "\n\n");
		}
		
		self::totals();
		$end = microtime();
		echo self::text('white', 'Total Time: ' . ($end - $start))
		    .self::text('normal', "\n"); 
	}
}