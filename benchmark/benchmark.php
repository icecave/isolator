#!/usr/bin/env php
<?php
require __DIR__ . '/../vendor/autoload.php';

define('ITERATIONS', 20000);

$isolator = Icecave\Isolator\Isolator::get();

$reflector = new ReflectionClass($isolator);
echo $reflector->getFilename() . PHP_EOL;

class TestClass
{
    public function __construct($param = null)
    {
    }
}

function runBenchmark($name, callable $work)
{
    printf('-- %s --' . PHP_EOL, $name);

    $total = 0;

    ob_start();

    for ($i = 0; $i < ITERATIONS; ++$i) {
        $start = microtime(true);
        $work();
        $end = microtime(true);
        $total += $end - $start;
    }

    ob_end_clean();

    printf('Iterations:   %d' . PHP_EOL, ITERATIONS);
    printf('Elapsed Time: %.15f' . PHP_EOL, $total);
    printf('Average Time: %.15f' . PHP_EOL, $total / ITERATIONS);
    printf(PHP_EOL);
}

runBenchmark(
    'fixed arguments',
    function () use ($isolator) {
        $isolator->strlen('foo');
    }
);

runBenchmark(
    'variable arguments',
    function () use ($isolator) {
        $isolator->sprintf('%d %d %d %d %d', 1, 2, 3, 4, 5);
    }
);

runBenchmark(
    'reference parameter',
    function () use ($isolator) {
        $matches = array();
        $isolator->preg_match('/./', 'a', $matches);
    }
);

runBenchmark(
    'echo',
    function () use ($isolator) {
        $isolator->echo('.');
    }
);

runBenchmark(
    'new',
    function () use ($isolator) {
        $isolator->new('TestClass');
    }
);

runBenchmark(
    'new with arguments',
    function () use ($isolator) {
        $isolator->new('TestClass', 'arg');
    }
);

runBenchmark(
    'eval',
    function () use ($isolator) {
        $isolator->eval('return 1;');
    }
);
