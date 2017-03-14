<?php

namespace Feather;

// consider the following FizzBuzz implementation:

function fizzBuzz($i)
{
    $output = '';
    if ( $i % 3 == 0 ) $output = 'Fizz';
    if ( $i % 5 == 0 ) $output .= 'Buzz';

    return ($output ?: $i);
}

// a Feather test suite for the function may look something like this:

run( 'Function tests',

    test('FizzBuzz returns "Fizz" if given value is a multiple of 3', function ($t) {
        $multiplesOf3Only = [3, 6, 9, 12, 18];
        foreach ($multiplesOf3Only as $fizzValue) {
            $t->assertEqual('Fizz', FizzBuzz($fizzValue),
                "FizzBuzz failed to return 'Fizz' when given {$fizzValue} (which is a multiple of 3)");
        }
    }),

    test('FizzBuzz returns "Buzz" if given value is a multiple of 5', function ($t) {
        $multplesOf5Only = [5, 10, 20, 25, 35];
        foreach ($multplesOf5Only as $buzzValue) {
            $t->assertEqual('Buzz', FizzBuzz($buzzValue),
                "FizzBuzz failed to return 'Buzz' when given {$buzzValue} (which is a multiple of 5)");
        }
    }),

    test('FizzBuzz returns "FizzBuzz" if given value is a multiple of 3 and 5', function ($t) {
        $multiplesOf3And5 = [15, 30, 45, 60, 75];
        foreach ($multiplesOf3And5 as $fizzBuzzValue) {
            $t->assertEqual('FizzBuzz', FizzBuzz($fizzBuzzValue),
                "FizzBuzz failed to return 'FizzBuzz' when given {$fizzBuzzValue} (which is a multiple of 3 and 5)");
        }
    }),

    test('FizzBuzz returns the value if given value is not a multiple of 3 or 5', function ($t) {
        $doubleDigitPrimes = [11, 13, 17, 19, 23];
        foreach ($doubleDigitPrimes as $nonFizzBuzzValue) {
            $t->assertEqual($nonFizzBuzzValue, FizzBuzz($nonFizzBuzzValue),
                "FizzBuzz failed to return {$nonFizzBuzzValue} string when given {$nonFizzBuzzValue} (which is prime)");
        }
    })

);
