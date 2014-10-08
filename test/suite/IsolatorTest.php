<?php
namespace Icecave\Isolator;

use DateTime;
use PHPUnit_Framework_TestCase;
use SplObjectStorage;

/**
 * @covers Icecave\Isolator\Isolator
 * @covers Icecave\Isolator\Detail\AbstractIsolator
 */
class IsolatorTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->isolator = new Isolator();
    }

    public function tearDown()
    {
        Isolator::set(null);
    }

    public function testCall()
    {
        $this->assertSame(
            3,
            $this->isolator->strlen('foo')
        );
    }

    public function testCallWithReference()
    {
        $matches = array();

        $this->isolator->preg_match(
            '/.*/',
            'foo',
            $matches
        );

        $this->assertSame(
            array('foo'),
            $matches
        );
    }

    public function testCallWithVarArgs()
    {
        $this->assertSame(
            'a b c',
            $this->isolator->sprintf(
                '%s %s %s',
                'a',
                'b',
                'c'
            )
        );
    }

    public function testCallFunctionDefinedAfterIsolatorCreated()
    {
        if (function_exists('icecaveIsolatorPostGeneration')) {
            $this->markTestSkipped('This test can only be executed once.');
        }

        require __DIR__ . '/../src/function.php';

        $this->assertNull(
            $this->isolator->icecaveIsolatorPostGeneration()
        );

        $this->assertSame(
            $this->isolator->icecaveIsolatorPostGeneration(123),
            123
        );
    }

    public function testEcho()
    {
        $this->expectOutputString('Echo works!');

        $this->isolator->echo('Echo works!');
    }

    public function testEval()
    {
        $this->assertSame(
            3,
            $this->isolator->eval('return strlen("foo");')
        );
    }

    public function testInclude()
    {
        $this->assertFalse(
            class_exists('Icecave\Isolator\ClassA', false)
        );

        $this->assertSame(
            'returnValueA',
            $this->isolator->include(__DIR__ . '/../src/ClassA.php')
        );

        $this->assertTrue(
            class_exists('Icecave\Isolator\ClassA', false)
        );
    }

    public function testIncludeOnce()
    {
        $this->assertFalse(
            class_exists('Icecave\Isolator\ClassB', false)
        );

        $this->assertSame(
            'returnValueB',
            $this->isolator->include_once(__DIR__ . '/../src/ClassB.php')
        );

        $this->assertTrue(
            class_exists('Icecave\Isolator\ClassB', false)
        );
    }

    public function testRequire()
    {
        $this->assertFalse(
            class_exists('Icecave\Isolator\ClassC', false)
        );

        $this->assertSame(
            'returnValueC',
            $this->isolator->require(__DIR__ . '/../src/ClassC.php')
        );

        $this->assertTrue(
            class_exists('Icecave\Isolator\ClassC', false)
        );
    }

    public function testRequireOnce()
    {
        $this->assertFalse(
            class_exists('Icecave\Isolator\ClassD', false)
        );

        $this->assertSame(
            'returnValueD',
            $this->isolator->require_once(__DIR__ . '/../src/ClassD.php')
        );

        $this->assertTrue(
            class_exists('Icecave\Isolator\ClassD', false)
        );
    }

    public function testNew()
    {
        $this->assertEquals(
            new SplObjectStorage(),
            $this->isolator->new('SplObjectStorage')
        );
    }

    public function testNewWithConstructorArguments()
    {
        $this->assertEquals(
            new DateTime('2014-01-01 01:02:03 GMT'),
            $this->isolator->new('DateTime', '2014-01-01 01:02:03 GMT')
        );
    }

    public function testGet()
    {
        $this->assertSame(
            $this->isolator,
            Isolator::get($this->isolator)
        );

        $globalIsolator = Isolator::get();

        $this->assertInstanceOf(
            'Icecave\Isolator\Isolator',
            $globalIsolator
        );

        $this->assertNotSame(
            $this->isolator,
            $globalIsolator
        );

        $this->assertSame(
            $globalIsolator,
            Isolator::get()
        );
    }
}
