<?php
namespace Icecave\Isolator;

use ReflectionClass;
use ReflectionFunction;
use PHPUnit_Framework_TestCase;
use Phake;

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
        $this->assertFalse(class_exists(__NAMESPACE__.'\TestFixture\ClassA', false));

        $this->assertSame(
            'returnValueA',
            $isolator->include(__DIR__.'/../../../lib/Icecave/Isolator/TestFixture/ClassA.php')
        );
        $this->assertTrue(class_exists(__NAMESPACE__.'\TestFixture\ClassA', false));
    }

    public function testIncludeOnce()
    {
        $isolator = new Isolator;
        $this->assertFalse(class_exists(__NAMESPACE__.'\TestFixture\ClassB', false));

        $this->assertSame(
            'returnValueB',
            $isolator->include_once(__DIR__.'/../../../lib/Icecave/Isolator/TestFixture/ClassB.php')
        );
        $this->assertTrue(class_exists(__NAMESPACE__.'\TestFixture\ClassB', false));
    }

    public function testRequire()
    {
        $isolator = new Isolator;
        $this->assertFalse(class_exists(__NAMESPACE__.'\TestFixture\ClassC', false));

        $this->assertSame(
            'returnValueC',
            $isolator->require(__DIR__.'/../../../lib/Icecave/Isolator/TestFixture/ClassC.php')
        );
        $this->assertTrue(class_exists(__NAMESPACE__.'\TestFixture\ClassC', false));
    }

    public function testRequireOnce()
    {
        $isolator = new Isolator;
        $this->assertFalse(class_exists(__NAMESPACE__.'\TestFixture\ClassD', false));

        $this->assertSame(
            'returnValueD',
            $isolator->require_once(__DIR__.'/../../../lib/Icecave/Isolator/TestFixture/ClassD.php')
        );
        $this->assertTrue(class_exists(__NAMESPACE__.'\TestFixture\ClassD', false));
    }

    public function testGet()
    {
        $isolator = new Isolator;
        $this->assertSame($isolator, Isolator::get($isolator));
        $this->assertInstanceOf(__NAMESPACE__ . '\Isolator', Isolator::get(null));
    }

    public function testGetIsolator()
    {
        $isolator = Isolator::getIsolator();
        $this->assertInstanceOf(__NAMESPACE__ . '\Isolator', $isolator);
        $this->assertSame($isolator, Isolator::getIsolator());
    }

    public function testGetIsolatorNoReferences()
    {
        $isolator = Isolator::getIsolator(false);
        $this->assertSame(__NAMESPACE__ . '\Isolator', get_class($isolator));
    }

    public function testGetIsolatorExistingInstance()
    {
        $isolator = Isolator::getIsolator(false);
        $this->assertInstanceOf(__NAMESPACE__ . '\Isolator', $isolator);
        $this->assertSame($isolator, Isolator::getIsolator(false));
    }

    public function testGetIsolatorNewInstance()
    {
        $generator = Phake::mock(__NAMESPACE__ . '\Generator');
        Phake::when($generator)
            ->generateClass(Phake::anyParameters())
            ->thenReturn(new ReflectionClass(__NAMESPACE__ . '\Isolator'));

        $functions = array(
            'internal' => array(
                'strlen'
            )
        );

        $reflectors = array(
            new ReflectionFunction('strlen')
        );

        $internalIsolator = Phake::mock(__NAMESPACE__ . '\Isolator');
        Phake::when($internalIsolator)
            ->get_defined_functions()
            ->thenReturn($functions);

        $isolator = Isolator::getIsolator(true, $generator, $internalIsolator);
        $this->assertInstanceOf(__NAMESPACE__ . '\Isolator', $isolator);

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
