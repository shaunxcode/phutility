<?php
namespace Phutility\Test\Func;

require_once '../src/Func.php';

use Phutility\Test; 
use Phutility\Func as F;

$func = F::rest(function($a, $b, $c, $rest) {
	return $rest;
});

Test::assert("rest args is working", $func(1, 2, 3, 4, 5, 6), array(4, 5, 6));

$func2 = F::rest(function($a, $b, $c, $rest) {
	return $a;
});

Test::assert("other args are correct", $func2(1, 2, 3), 1);

$func3 = F::rest(function($a, $b, $c){});

Test::throwsException("incorrect arg num in func", function() use($func3) { $func3(1); });

Test::throwsException("1nd param must be closure", function() { F::rest('apple'); });

$func4 = F::options(function($options) {
	return $options;
});

test::assert("options passes through", $func4('name', 'peter', 'age', 13), array('name' => 'peter', 'age' => 13));

$func5 = F::options(function($age, $weight, $options) {
	return array('age' => $age, 'weight' => $weight, 'options' => $options);
});

test::assert("extra params", $func5(5, 500, 'name', 'peter', 'hair', 'red'), array('age' => 5, 'weight' => 500, 'options' => array('name' => 'peter', 'hair' => 'red')));

$keyword = F::keyword(function($age, $eyeColor) {
	return array('age' => $age, 'eyeColor' => $eyeColor);
});

test::assert("keyword args normal", $keyword(age, 25, eyeColor, 'blue'), array('age' => 25, 'eyeColor' => 'blue'));
test::assert("keyword args reversed", $keyword(eyeColor, 'blue', age, 25), array('age' => 25, 'eyeColor' => 'blue'));

$argsFunc = F::args(function($args) { 
	return $args;
});

test::assert("args works", $argsFunc(1, 2, 3), array(1, 2, 3));

class Circle {}
class Square {}
const Circle = 'Phutility\Test\Func\Circle';
const Square = 'Phutility\Test\Func\Square';
 
$multi = F::matchType(
	Circle, Square, 
	function($c, $s) {
		return "MATCHED CIRCLE SQUARE";
	},
	Square, 
	function($s) {
		return "MATCHED SQUARE";
	});

test::assert("Match Square", $multi(new Square), "MATCHED SQUARE");
test::assert("Match Circle Square", $multi(new Circle, new Square), "MATCHED CIRCLE SQUARE");

$fact = F::match(
	0, function() { return 1;},
	F::x, function($x) use(&$fact) { return $x * $fact($x - 1);});
	
test::assert("factorial works", $fact(5), 120);