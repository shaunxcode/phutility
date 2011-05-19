<?php
namespace Phutility\Test\Func;

require_once '../src/Appos.php';
require_once '../src/Func.php';

use Phutility\Test; 
use Phutility\Func as F;
use Phutility\Appos as A;

Test::assert("appos works", A::create(array('a', 1, 'b', 2)), array('a' => 1, 'b' => 2));

$funk = function() { 
	return A::create(func_get_args());
};

Test::assert("from func_get_args", $funk('a', 1, 'b', 2), array('a' => 1, 'b' => 2));

$funk2 = F::rest(2, function($a, $b, $options) { 
	return A::create($options);
});

Test::assert("mixed with func as options", $funk2(1, 2, 'a', 1, 'b', 2), array('a' => 1, 'b' => 2));

Test::throwsException("wrong num of args", function(){A::create(array('a', 1, 'b'));});