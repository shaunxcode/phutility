<?php

namespace Phutility\Test\Invokable;

require_once '../src/Invokable.php';

use Phutility\Test; 
use Phutility\Invokable as I;

class apple {
	public function takeBite($rodent) {
		return "$rodent took bite";
	}
}

class container {
	public function getApple() {
		return new apple;
	}
}

$x = new container;

$animal = 'rat';

$i = I::getApple()->takeBite($animal);

Test::assert("Nested invokable", $i($x), 'rat took bite');
Test::assert("obj first, index 2nd", $i($x, 3), 'rat took bite');
Test::assert("obj second, index 1st", $i(3, $x), 'rat took bite');