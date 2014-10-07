#!/usr/bin/env php
<?php
require __DIR__ . '/../vendor/autoload.php';

define('ITERATIONS', 10000);

$isolator = Icecave\Isolator\Isolator::get();

function runBenchmark($name, callable $work)
{
    printf('-- %s --' . PHP_EOL, $name);

    $start = microtime(true);

    $work();

    $end = microtime(true);

    printf('Iterations:   %s' . PHP_EOL, ITERATIONS);
    printf('Elapsed Time: %s' . PHP_EOL, $end - $start);
    printf(PHP_EOL);
}

runBenchmark(
    'fixed arguments',
    function () use ($isolator) {
        for ($i = 0; $i < ITERATIONS; ++$i) {
            $isolator->strlen('foo');
        }
    }
);

runBenchmark(
    'variable arguments',
    function () use ($isolator) {
        for ($i = 0; $i < ITERATIONS; ++$i) {
            $isolator->sprintf('%d %d %d %d %d', 1, 2, 3, 4, 5);
        }
    }
);

runBenchmark(
    'reference parameter',
    function () use ($isolator) {
        $matches = array();
        for ($i = 0; $i < ITERATIONS; ++$i) {
            $isolator->preg_match('/./', 'a', $matches);
        }
    }
);
