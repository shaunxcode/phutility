<?php
namespace Phutility\Test\Func;

require_once '../src/Func.php';

use Phutility\Test; 
use Phutility\Func as F;

$func = F::rest(3, function($a, $b, $c, $rest) {
	return $rest;
});

Test::assert("rest args is working", $func(1, 2, 3, 4, 5, 6), array(4, 5, 6));

$func2 = F::rest(3, function($a, $b, $c, $rest) {
	return $a;
});

Test::assert("other args are correct", $func2(1, 2, 3), 1);

$func3 = F::rest(4, function($a, $b){});

Test::throwsException("incorrect arg num in func", function() use($func3) { $func3(1,2); });

Test::throwsException("2nd param must be closure", function() { F::rest(1, 'apple'); });

Test::throwsException("1st param must be int, pass float", function() { F::rest(1.3, function(){});});

Test::throwsException("1st param must be int, pass string", function() { F::rest('donkey', function(){});});

$func4 = F::options(function($options) {
	return $options;
});

test::assert("options passes through", $func4('name', 'peter', 'age', 13), array('name' => 'peter', 'age' => 13));

$func5 = F::options(2, function($age, $weight, $options) {
	return array('age' => $age, 'weight' => $weight, 'options' => $options);
});

test::assert("extra params", $func5(5, 500, 'name', 'peter', 'hair', 'red'), array('age' => 5, 'weight' => 500, 'options' => array('name' => 'peter', 'hair' => 'red')));