<?php
namespace Test;

require_once 'src/Invokable.php';

use Phunderscore\Invokable as I;

class TestClass {
	private $name;
	public $age;
	
	public function __construct($name, $age = 30) {
		$this->name = $name;
		$this->age = $age;
	}
	
	public function methodName($var) {
		echo "{$this->name} is an {$var}\n";
	}
}

$x = I::methodName('apple');

$x(new TestClass('peter'));
$x(new TestClass('walter'));