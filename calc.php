<?php

use igorw\rpn;

require 'vendor/autoload.php';

if ($argc !== 2) {
    echo "Usage: php calc.php EXPR\n";
    exit(1);
}

$input = $argv[1];

$operators = [
    '+' => ['precedence' => 0, 'associativity' => 'left'],
    '-' => ['precedence' => 0, 'associativity' => 'left'],
    '*' => ['precedence' => 1, 'associativity' => 'left'],
    '/' => ['precedence' => 1, 'associativity' => 'left'],
    '%' => ['precedence' => 1, 'associativity' => 'left'],
    '^' => ['precedence' => 2, 'associativity' => 'right'],
];

$tokens = rpn\tokenize($input);
$rpn = rpn\shunting_yard($tokens, $operators);
$result = rpn\execute($rpn);

echo "$result\n";
