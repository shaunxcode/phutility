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
	
	public static function assert($description, $val, $isVal = true) {
		$pass = $val == $isVal;
				
		if($pass) { 
			self::$passCount++;
		} else {
			self::$failCount++;
		}
		
		echo self::text('white', $description . ' [' 
		   . self::text($pass ? 'green' : 'red', $pass ? 'PASS' : 'FAIL'))
		   . self::text('white', ']') 
		   . self::text('normal', "\n");
			
		return $pass;
	}
	
	public static function totals() {
		echo self::text('green', 'Total Passed: ' . self::$passCount) . "\n" 
		   . self::text('red', 'Total Failed: ' . self::$failCount) 
		   . self::text('normal', "\n"); 
	}
}