<?php

namespace Phutility\Test\Node;

require_once '../src/Node.php';

use Phutility\Test;
use Phutility\Node; 

function node($type, $vals) { return new Node($type, $vals); }
function x() { return node('x', func_get_args()); }
function y() { return node('y', func_get_args()); }

$graph = x(y(x(y('a', 'b', 'c'))));

Test::assert("can navigate graph", $graph->y->x->y->first(), 'a');
Test::assert("get last element", $graph->y->x->y->last(), 'c');
Test::assert("get nth element", $graph->y->x->y->nth(1), 'b');