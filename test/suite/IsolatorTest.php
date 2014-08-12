<?php
namespace Icecave\Isolator;

use DateTime;
use Phake;
use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ReflectionFunction;

class IsolatorTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        parent::tearDown();
        Isolator::resetIsolator();
    }

    public function testCall()
    {
        $isolator = new Isolator;
        $this->assertSame(3, $isolator->strlen('foo'));
    }

    public function testEcho()
    {
        $isolator = new Isolator;
        $this->expectOutputString('Echo works!');
        $isolator->echo('Echo works!');
    }

    public function testEval()
    {
        $isolator = new Isolator;
        $this->assertSame(3, $isolator->eval('return strlen("foo");'));
    }

    public function testInclude()
    {
        $isolator = new Isolator;
        $this->assertFalse(class_exists('Icecave\Isolator\ClassA', false));

        $this->assertSame(
            'returnValueA',
            $isolator->include(__DIR__ . '/../src/ClassA.php')
        );
        $this->assertTrue(class_exists('Icecave\Isolator\ClassA', false));
    }

    public function testIncludeOnce()
    {
        $isolator = new Isolator;
        $this->assertFalse(class_exists('Icecave\Isolator\ClassB', false));

        $this->assertSame(
            'returnValueB',
            $isolator->include_once(__DIR__ . '/../src/ClassB.php')
        );
        $this->assertTrue(class_exists('Icecave\Isolator\ClassB', false));
    }

    public function testRequire()
    {
        $isolator = new Isolator;
        $this->assertFalse(class_exists('Icecave\Isolator\ClassC', false));

        $this->assertSame(
            'returnValueC',
            $isolator->require(__DIR__ . '/../src/ClassC.php')
        );
        $this->assertTrue(class_exists('Icecave\Isolator\ClassC', false));
    }

    public function testRequireOnce()
    {
        $isolator = new Isolator;
        $this->assertFalse(class_exists('Icecave\Isolator\ClassD', false));

        $this->assertSame(
            'returnValueD',
            $isolator->require_once(__DIR__ . '/../src/ClassD.php')
        );
        $this->assertTrue(class_exists('Icecave\Isolator\ClassD', false));
    }

    public function testNew()
    {
        $isolator = new Isolator;

        $this->assertEquals(
            new DateTime('2014-01-01 01:02:03 GMT'),
            $isolator->new('DateTime', '2014-01-01 01:02:03 GMT')
        );
    }

    public function testGet()
    {
        $isolator = new Isolator;
        $this->assertSame($isolator, Isolator::get($isolator));
        $this->assertInstanceOf('Icecave\Isolator\Isolator', Isolator::get(null));
    }

    public function testGetIsolator()
    {
        $isolator = Isolator::getIsolator();
        $this->assertInstanceOf('Icecave\Isolator\Isolator', $isolator);
        $this->assertSame($isolator, Isolator::getIsolator());
    }

    public function testGetIsolatorNoReferences()
    {
        $isolator = Isolator::getIsolator(false);
        $this->assertSame('Icecave\Isolator\Isolator', get_class($isolator));
    }

    public function testGetIsolatorExistingInstance()
    {
        $isolator = Isolator::getIsolator(false);
        $this->assertInstanceOf('Icecave\Isolator\Isolator', $isolator);
        $this->assertSame($isolator, Isolator::getIsolator(false));
    }

    public function testGetIsolatorNewInstance()
    {
        $generator = Phake::mock('Icecave\Isolator\Generator');
        Phake::when($generator)
            ->generateClass(Phake::anyParameters())
            ->thenReturn(new ReflectionClass('Icecave\Isolator\Isolator'));

        $functions = array(
            'internal' => array(
                'strlen'
            )
        );

        $reflectors = array(
            new ReflectionFunction('strlen')
        );

        $internalIsolator = Phake::mock('Icecave\Isolator\Isolator');
        Phake::when($internalIsolator)
            ->get_defined_functions()
            ->thenReturn($functions);

        $isolator = Isolator::getIsolator(true, $generator, $internalIsolator);
        $this->assertInstanceOf('Icecave\Isolator\Isolator', $isolator);

        Phake::inOrder(
            Phake::verify($internalIsolator)->get_defined_functions()
            , Phake::verify($generator)->generateClass($reflectors)
        );

        // Invoking a second time should not produce any additional calls to the generator or isolator ...
        Phake::verifyNoFurtherInteraction($generator);
        Phake::verifyNoFurtherInteraction($internalIsolator);
        $this->assertSame($isolator, Isolator::getIsolator(true, $generator, $isolator));
    }

    public function testClassName()
    {
        $isolator = Isolator::getIsolator();

        $this->assertSame(get_class($isolator), Isolator::className());
    }
}
