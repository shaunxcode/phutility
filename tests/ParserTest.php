<?php

namespace Phutility\Test\Parser;

require_once '../src/Parser.php';

use Phutility\Test;
use Phutility\Parser; 

Test::assert(
	"test sub expression parsing", 
	Parser::subExpressions("a b(c) dude e (f g (h i (k l m())))"), 
	array(a, b, array(c), dude, e, array(f, g, array(h, i, array(k, l, m, array())))));
