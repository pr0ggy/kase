<?php

namespace Kase;

// Kase includes the Kanta assertion library, but feel free to use any exception-based library
use Kanta\Validation as v;

// consider the following FizzBuzz implementation:

function fizzBuzz($i)
{
    $output = '';
    if ( $i % 3 == 0 ) $output = 'Fizz';
    if ( $i % 5 == 0 ) $output .= 'Buzz';

    return ($output ?: $i);
}

// a Kase test suite for the function may look something like this:

return runner( 'Function tests',

    test('FizzBuzz returns "Fizz" if given value is a multiple of 3', function () {
        $multiplesOf3Only = [3, 6, 9, 12, 18];
        foreach ($multiplesOf3Only as $fizzValue) {
            v\assert([
                'that' => FizzBuzz($fizzValue),
                'satisfies' => v\is('Fizz'),
                'orFailBecause' => "FizzBuzz failed to return 'Fizz' when given {$fizzValue} (which is a multiple of 3)"
            ]);
        }
    }),

    test('FizzBuzz returns "Buzz" if given value is a multiple of 5', function () {
        $multplesOf5Only = [5, 10, 20, 25, 35];
        foreach ($multplesOf5Only as $buzzValue) {
            v\assert([
                'that' => FizzBuzz($buzzValue),
                'satisfies' => v\is('Buzz'),
                'orFailBecause' => "FizzBuzz failed to return 'Buzz' when given {$buzzValue} (which is a multiple of 5)"
            ]);
        }
    }),

    test('FizzBuzz returns "FizzBuzz" if given value is a multiple of 3 and 5', function () {
        $multiplesOf3And5 = [15, 30, 45, 60, 75];
        foreach ($multiplesOf3And5 as $fizzBuzzValue) {
            v\assert([
                'that' => FizzBuzz($fizzBuzzValue),
                'satisfies' => v\is('FizzBuzz'),
                'orFailBecause' => "FizzBuzz failed to return 'FizzBuzz' when given {$fizzBuzzValue} (which is a multiple of 3 and 5)"
            ]);
        }
    }),

    test('FizzBuzz returns the value if given value is not a multiple of 3 or 5', function () {
        $doubleDigitPrimes = [11, 13, 17, 19, 23];
        foreach ($doubleDigitPrimes as $nonFizzBuzzValue) {
            v\assert([
                'that' => FizzBuzz($nonFizzBuzzValue),
                'satisfies' => v\is($nonFizzBuzzValue),
                'orFailBecause' => "FizzBuzz failed to return {$nonFizzBuzzValue} string when given {$nonFizzBuzzValue} (which is prime)"
            ]);
        }
    })

);
