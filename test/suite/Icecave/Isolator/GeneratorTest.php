<?php
namespace Icecave\Isolator;

use ReflectionFunction;
use PHPUnit_Framework_TestCase;
use Phake;

class GeneratorTest extends PHPUnit_Framework_TestCase
{
  public function setUp()
  {
    $this->_isolator = Phake::mock(__NAMESPACE__ . '\Isolator');
    $this->_generator = Phake::partialMock(
      __NAMESPACE__ . '\Generator'
      , 5
      , $this->_isolator
    );
  }

  public function testGenerateClass()
  {
    $reflector = Phake::mock('ReflectionFunction');
    $reflectors = array($reflector);

    Phake::when($this->_generator)
      ->requiresIsolatorProxy(Phake::anyParameters())
      ->thenReturn(true);

    Phake::when($this->_generator)
      ->generateProxyMethod(Phake::anyParameters())
      ->thenReturn('/* method goes here */');

    $this->setExpectedException('ReflectionException', 'Class ' . __NAMESPACE__ . '\TestIsolatorClass does not exist');
    try {
      $this->_generator->generateClass($reflectors, 'TestIsolatorClass');
    } catch (ReflectionException $e) {
      $expectedCode  = 'namespace Icecave\Isolator {' . PHP_EOL;
      $expectedCode .= 'class TestIsolatorClass extends Isolator {' . PHP_EOL;
      $expectedCode .= PHP_EOL;
      $expectedCode .= '/* method goes here */' . PHP_EOL;
      $expectedCode .= '} // End class.' . PHP_EOL;
      $expectedCode .= '} // End namespace.' . PHP_EOL;

      Phake::inOrder(
        Phake::verify($this->_generator)->requiresIsolatorProxy($reflector)
        , Phake::verify($this->_generator)->generateProxyMethod($reflector)
        , Phake::verify($this->_isolator)->eval($expectedCode)
      );
      throw $e;
    }
  }

  public function testInspect()
  {
    $reflector = Phake::mock('ReflectionFunction');
    $parameter1 = Phake::mock('ReflectionParameter');
    $parameter2 = Phake::mock('ReflectionParameter');

    Phake::when($reflector)
      ->getParameters()
      ->thenReturn(array($parameter1, $parameter2));

    Phake::when($parameter1)
      ->isPassedByReference()
      ->thenReturn(false);

    Phake::when($parameter2)
      ->isPassedByReference()
      ->thenReturn(true);

    Phake::when($parameter1)
      ->isOptional()
      ->thenReturn(false);

    Phake::when($parameter2)
      ->isOptional()
      ->thenReturn(true);

    $expected = array(
      1
      , 2
      , array(false, true)
    );

    $this->assertSame($expected, $this->_generator->inspect($reflector));

    Phake::inOrder(
      Phake::verify($reflector)->getParameters()
      , Phake::verify($parameter1)->isPassedByReference()
      , Phake::verify($parameter1)->isOptional()
      , Phake::verify($parameter1)->getName()
      , Phake::verify($parameter2)->isPassedByReference()
      , Phake::verify($parameter2)->isOptional()
      , Phake::verify($parameter2)->getName()
    );
  }

  public function testInspectEllipsis()
  {
    $reflector = Phake::mock('ReflectionFunction');
    $parameter1 = Phake::mock('ReflectionParameter');
    $parameter2 = Phake::mock('ReflectionParameter');

    Phake::when($reflector)
      ->getParameters()
      ->thenReturn(array($parameter1, $parameter2));

    Phake::when($parameter1)
      ->isPassedByReference()
      ->thenReturn(false);

    Phake::when($parameter2)
      ->isPassedByReference()
      ->thenReturn(false);

    Phake::when($parameter1)
      ->isOptional()
      ->thenReturn(false);

    Phake::when($parameter2)
      ->isOptional()
      ->thenReturn(false);

    Phake::when($parameter2)
      ->getName()
      ->thenReturn('...');

    $expected = array(
      2
      , 6
      , array(
        false
        , false
        , false
        , false
        , false
        , false
      )
    );

    $this->assertSame($expected, $this->_generator->inspect($reflector));

    $parameter2Verifier = Phake::verify($parameter2, Phake::times(2));

    Phake::inOrder(
      Phake::verify($reflector)->getParameters()
      , Phake::verify($parameter1)->isPassedByReference()
      , Phake::verify($parameter1)->isOptional()
      , Phake::verify($parameter1)->getName()
      , $parameter2Verifier->isPassedByReference()
      , Phake::verify($parameter2)->isOptional()
      , Phake::verify($parameter2)->getName()
      , $parameter2Verifier->isPassedByReference()
    );
  }

  public function testInspectEllipsisOptional()
  {
    $reflector = Phake::mock('ReflectionFunction');
    $parameter1 = Phake::mock('ReflectionParameter');
    $parameter2 = Phake::mock('ReflectionParameter');

    Phake::when($reflector)
      ->getParameters()
      ->thenReturn(array($parameter1, $parameter2));

    Phake::when($parameter1)
      ->isPassedByReference()
      ->thenReturn(false);

    Phake::when($parameter2)
      ->isPassedByReference()
      ->thenReturn(false);

    Phake::when($parameter1)
      ->isOptional()
      ->thenReturn(false);

    Phake::when($parameter2)
      ->isOptional()
      ->thenReturn(true);

    Phake::when($parameter2)
      ->getName()
      ->thenReturn('...');

    $expected = array(
      1
      , 6
      , array(
        false
        , false
        , false
        , false
        , false
        , false
      )
    );

    $this->assertSame($expected, $this->_generator->inspect($reflector));

    $parameter2Verifier = Phake::verify($parameter2, Phake::times(2));

    Phake::inOrder(
      Phake::verify($reflector)->getParameters()
      , Phake::verify($parameter1)->isPassedByReference()
      , Phake::verify($parameter1)->isOptional()
      , Phake::verify($parameter1)->getName()
      , $parameter2Verifier->isPassedByReference()
      , Phake::verify($parameter2)->isOptional()
      , Phake::verify($parameter2)->getName()
      , $parameter2Verifier->isPassedByReference()
    );
  }

  public function testInspectEllipsisReference()
  {
    $reflector = Phake::mock('ReflectionFunction');
    $parameter1 = Phake::mock('ReflectionParameter');
    $parameter2 = Phake::mock('ReflectionParameter');

    Phake::when($reflector)
      ->getParameters()
      ->thenReturn(array($parameter1, $parameter2));

    Phake::when($parameter1)
      ->isPassedByReference()
      ->thenReturn(false);

    Phake::when($parameter2)
      ->isPassedByReference()
      ->thenReturn(true);

    Phake::when($parameter1)
      ->isOptional()
      ->thenReturn(false);

    Phake::when($parameter2)
      ->isOptional()
      ->thenReturn(false);

    Phake::when($parameter2)
      ->getName()
      ->thenReturn('...');

    $expected = array(
      2
      , 6
      , array(
        false
        , true
        , true
        , true
        , true
        , true
      )
    );

    $this->assertSame($expected, $this->_generator->inspect($reflector));

    $parameter2Verifier = Phake::verify($parameter2, Phake::times(2));

    Phake::inOrder(
      Phake::verify($reflector)->getParameters()
      , Phake::verify($parameter1)->isPassedByReference()
      , Phake::verify($parameter1)->isOptional()
      , Phake::verify($parameter1)->getName()
      , $parameter2Verifier->isPassedByReference()
      , Phake::verify($parameter2)->isOptional()
      , Phake::verify($parameter2)->getName()
      , $parameter2Verifier->isPassedByReference()
    );
  }

  public function testRequiresIsolatorProxyDisabled()
  {
    $reflector = Phake::mock('ReflectionFunction');
    Phake::when($reflector)
      ->isDisabled()
      ->thenReturn(true);

    $this->assertFalse($this->_generator->requiresIsolatorProxy($reflector));

    Phake::verify($reflector)->isDisabled();
  }

  public function testRequiresIsolatorProxyReturnsReference()
  {
    $reflector = Phake::mock('ReflectionFunction');
    Phake::when($reflector)
      ->isDisabled()
      ->thenReturn(false);

    Phake::when($reflector)
      ->returnsReference()
      ->thenReturn(true);

    $this->assertTrue($this->_generator->requiresIsolatorProxy($reflector));

    Phake::inOrder(
      Phake::verify($reflector)->isDisabled()
      , Phake::verify($reflector)->returnsReference()
    );
  }

  public function testRequiresIsolatorProxyReferenceParameter()
  {
    $reflector = Phake::mock('ReflectionFunction');
    $parameter1 = Phake::mock('ReflectionParameter');
    $parameter2 = Phake::mock('ReflectionParameter');

    Phake::when($reflector)
      ->isDisabled()
      ->thenReturn(false);

    Phake::when($reflector)
      ->returnsReference()
      ->thenReturn(false);

    Phake::when($reflector)
      ->getParameters()
      ->thenReturn(array($parameter1, $parameter2));

    Phake::when($parameter1)
      ->isPassedByReference()
      ->thenReturn(false);

    Phake::when($parameter2)
      ->isPassedByReference()
      ->thenReturn(true);

    $this->assertTrue($this->_generator->requiresIsolatorProxy($reflector));

    Phake::inOrder(
      Phake::verify($reflector)->isDisabled()
      , Phake::verify($reflector)->returnsReference()
      , Phake::verify($reflector)->getParameters()
      , Phake::verify($parameter1)->isPassedByReference()
      , Phake::verify($parameter2)->isPassedByReference()
    );
  }

  public function testRequiresIsolatorProxyNoReferenceParameters()
  {
    $reflector = Phake::mock('ReflectionFunction');
    $parameter1 = Phake::mock('ReflectionParameter');
    $parameter2 = Phake::mock('ReflectionParameter');

    Phake::when($reflector)
      ->isDisabled()
      ->thenReturn(false);

    Phake::when($reflector)
      ->returnsReference()
      ->thenReturn(false);

    Phake::when($reflector)
      ->getParameters()
      ->thenReturn(array($parameter1, $parameter2));

    Phake::when($parameter1)
      ->isPassedByReference()
      ->thenReturn(false);

    Phake::when($parameter2)
      ->isPassedByReference()
      ->thenReturn(false);

    $this->assertFalse($this->_generator->requiresIsolatorProxy($reflector));

    Phake::inOrder(
      Phake::verify($reflector)->isDisabled()
      , Phake::verify($reflector)->returnsReference()
      , Phake::verify($reflector)->getParameters()
      , Phake::verify($parameter1)->isPassedByReference()
      , Phake::verify($parameter2)->isPassedByReference()
    );
  }

}
