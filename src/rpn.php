<?php

namespace igorw\rpn;

function tokenize($input)
{
    return explode(' ', trim(preg_replace('#[ ]+#', ' ', str_replace(['(', ')'], [' ( ', ' ) '], $input))));
}

function shunting_yard($tokens, array $operators)
{
    $stack = new \SplStack();

    foreach ($tokens as $token) {
        if (is_numeric($token)) {
            yield $token;
        } elseif (isset($operators[$token])) {
            $o1 = $token;
            while (stack_has_higher_operator($o1, $stack, $operators)) {
                yield $stack->pop();
            }
            $stack->push($o1);
        } elseif ('(' === $token) {
            $stack->push($token);
        } elseif (')' === $token) {
            while (count($stack) > 0 && '(' !== $stack->top()) {
                yield $stack->pop();
            }

            if (count($stack) === 0) {
                throw new \InvalidArgumentException(sprintf('Mismatched parenthesis in input: %s', json_encode($tokens)));
            }

            // pop off '('
            $stack->pop();
        } else {
            throw new \InvalidArgumentException(sprintf('Invalid token: %s', $token));
        }
    }

    while (stack_has_operator($stack, $operators)) {
        yield $stack->pop();
    }

    if (count($stack) > 0) {
        throw new \InvalidArgumentException(sprintf('Mismatched parenthesis or misplaced number in input: %s', json_encode($tokens)));
    }
}

function stack_has_operator(\SplStack $stack, array $operators)
{
    return count($stack) > 0 && ($top = $stack->top()) && isset($operators[$top]);
}

function stack_has_higher_operator($o1, \SplStack $stack, array $operators)
{
    if (!stack_has_operator($stack, $operators)) {
        return false;
    }

    $o2 = $stack->top();
    return has_lower_precedence($o1, $o2, $operators);
}

function has_lower_precedence($o1, $o2, array $operators)
{
    $op1 = $operators[$o1];
    $op2 = $operators[$o2];
    return ('left' === $op1['associativity'] && $op1['precedence'] === $op2['precedence']) || $op1['precedence'] < $op2['precedence'];
}

function execute($ops)
{
    $stack = new \SplStack();

    foreach ($ops as $op) {
        if (is_numeric($op)) {
            $stack->push((float) $op);
            continue;
        }

        switch ($op) {
            case '+':
                $stack->push($stack->pop() + $stack->pop());
                break;
            case '-':
                $n = $stack->pop();
                $stack->push($stack->pop() - $n);
                break;
            case '*':
                $stack->push($stack->pop() * $stack->pop());
                break;
            case '/':
                $n = $stack->pop();
                $stack->push($stack->pop() / $n);
                break;
            case '%':
                $n = $stack->pop();
                $stack->push($stack->pop() % $n);
                break;
            case '^':
                $n = $stack->pop();
                $stack->push(pow($stack->pop(), $n));
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Invalid operation: %s', $op));
                break;
        }
    }

    return $stack->top();
}
