<?php

namespace Phutility\Test\Invokable;

require_once '../src/Invokable.php';
require_once '../src/Obj.php';
require_once '../src/Test.php';

use Phutility\Test; 
use Phutility\Obj;
use Phutility\Invokable as I;

$x = Obj::create(
	'getFriend', function($self) { return $self->friend; },
	'friend', Obj::create(
		'age', 30, 
		'address', Obj::create(
			'street', '364 north 800 east')));

$i = I::getFriend()->address->street;

Test::assert("Nested property working", $x->getFriend()->address->street, '364 north 800 east');
Test::assert("Nested property working via invokable", $i($x), '364 north 800 east');
Test::totals();