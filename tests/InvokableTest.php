<?php

namespace Phutility\Test\Invokable;

require_once '../src/Invokable.php';
require_once '../src/Obj.php';

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

Test::assert("obj first, index 2nd", $i($x, 3), '364 north 800 east');
Test::assert("obj second, index 1st", $i(3, $x), '364 north 800 east');