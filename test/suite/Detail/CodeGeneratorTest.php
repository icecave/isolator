<?php
namespace Icecave\Isolator\Detail;

use Phake;
use PHPUnit_Framework_TestCase;
use ReflectionFunction;

class CodeGeneratorTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->generator = new CodeGenerator();
    }

    public function testGenerateClass()
    {
        $code = $this->generator->generate(
            'Foo',
            array()
        );

        $expectedCode  = '<?php' . PHP_EOL;
        $expectedCode .= PHP_EOL;
        $expectedCode .= 'use Icecave\Isolator\Detail\AbstractIsolator;' . PHP_EOL;
        $expectedCode .= PHP_EOL;
        $expectedCode .= 'class Foo extends AbstractIsolator' . PHP_EOL;
        $expectedCode .= '{' . PHP_EOL;
        $expectedCode .= '}' . PHP_EOL;

        $this->assertSame(
            $expectedCode,
            $code
        );
    }

    public function testGenerateClassInNamespace()
    {
        $code = $this->generator->generate(
            'Foo\Bar\Spam',
            array()
        );

        $expectedCode  = '<?php' . PHP_EOL;
        $expectedCode .= 'namespace Foo\Bar;' . PHP_EOL;
        $expectedCode .= PHP_EOL;
        $expectedCode .= 'use Icecave\Isolator\Detail\AbstractIsolator;' . PHP_EOL;
        $expectedCode .= PHP_EOL;
        $expectedCode .= 'class Spam extends AbstractIsolator' . PHP_EOL;
        $expectedCode .= '{' . PHP_EOL;
        $expectedCode .= '}' . PHP_EOL;

        $this->assertSame(
            $expectedCode,
            $code
        );
    }

    public function testGenerateMethod()
    {
        $code = $this->generator->generate(
            'Foo',
            array('strlen')
        );

        $expectedCode  = '<?php' . PHP_EOL;
        $expectedCode .= PHP_EOL;
        $expectedCode .= 'use Icecave\Isolator\Detail\AbstractIsolator;' . PHP_EOL;
        $expectedCode .= PHP_EOL;
        $expectedCode .= 'class Foo extends AbstractIsolator' . PHP_EOL;
        $expectedCode .= '{' . PHP_EOL;
        $expectedCode .= '    public function strlen($p0)' . PHP_EOL;
        $expectedCode .= '    {' . PHP_EOL;
        $expectedCode .= '        switch (func_num_args()) {' . PHP_EOL;
        $expectedCode .= '            case 1: return \strlen($p0);' . PHP_EOL;
        $expectedCode .= '        }' . PHP_EOL;
        $expectedCode .= '        return call_user_func_array(\'strlen\', func_get_args());' . PHP_EOL;
        $expectedCode .= '    }' . PHP_EOL;
        $expectedCode .= '}' . PHP_EOL;

        $this->assertSame(
            $expectedCode,
            $code
        );
    }

    public function testGenerateMethodWithReferenceParameter()
    {
        $code = $this->generator->generate(
            'Foo',
            array('ereg')
        );

        $expectedCode  = '<?php' . PHP_EOL;
        $expectedCode .= PHP_EOL;
        $expectedCode .= 'use Icecave\Isolator\Detail\AbstractIsolator;' . PHP_EOL;
        $expectedCode .= PHP_EOL;
        $expectedCode .= 'class Foo extends AbstractIsolator' . PHP_EOL;
        $expectedCode .= '{' . PHP_EOL;
        $expectedCode .= '    public function ereg($p0, $p1, &$p2 = null)' . PHP_EOL;
        $expectedCode .= '    {' . PHP_EOL;
        $expectedCode .= '        switch (func_num_args()) {' . PHP_EOL;
        $expectedCode .= '            case 2: return \ereg($p0, $p1);' . PHP_EOL;
        $expectedCode .= '            case 3: return \ereg($p0, $p1, $p2);' . PHP_EOL;
        $expectedCode .= '        }' . PHP_EOL;
        $expectedCode .= '        return call_user_func_array(\'ereg\', func_get_args());' . PHP_EOL;
        $expectedCode .= '    }' . PHP_EOL;
        $expectedCode .= '}' . PHP_EOL;

        $this->assertSame(
            $expectedCode,
            $code
        );
    }

    public function testGenerateMethodWithVarArgs()
    {
        $code = $this->generator->generate(
            'Foo',
            array('sprintf')
        );

        $expectedCode  = '<?php' . PHP_EOL;
        $expectedCode .= PHP_EOL;
        $expectedCode .= 'use Icecave\Isolator\Detail\AbstractIsolator;' . PHP_EOL;
        $expectedCode .= PHP_EOL;
        $expectedCode .= 'class Foo extends AbstractIsolator' . PHP_EOL;
        $expectedCode .= '{' . PHP_EOL;
        $expectedCode .= '    public function sprintf($p0, $p1)' . PHP_EOL;
        $expectedCode .= '    {' . PHP_EOL;
        $expectedCode .= '        switch (func_num_args()) {' . PHP_EOL;
        $expectedCode .= '            case 2: return \sprintf($p0, $p1);' . PHP_EOL;
        $expectedCode .= '        }' . PHP_EOL;
        $expectedCode .= '        return call_user_func_array(\'sprintf\', func_get_args());' . PHP_EOL;
        $expectedCode .= '    }' . PHP_EOL;
        $expectedCode .= '}' . PHP_EOL;

        $this->assertSame(
            $expectedCode,
            $code
        );
    }

    //     Phake::when($this->generator)
    //         ->requiresIsolatorProxy(Phake::anyParameters())
    //         ->thenReturn(true);

    //     Phake::when($this->generator)
    //         ->generateProxyMethod(Phake::anyParameters())
    //         ->thenReturn('/* method goes here */' . PHP_EOL);

    //     $expectedCode  = '<?php' . PHP_EOL;
    //     $expectedCode .= 'namespace Icecave\Isolator;' . PHP_EOL;
    //     $expectedCode .= PHP_EOL;
    //     $expectedCode .= 'class TestIsolatorClass extends Isolator' . PHP_EOL;
    //     $expectedCode .= '{' . PHP_EOL;
    //     $expectedCode .= '/* method goes here */' . PHP_EOL;
    //     $expectedCode .= '}' . PHP_EOL;

    //     $code = $this->generator->generateClass(
    //         'TestIsolatorClass',
    //         array('ereg')
    //     );

    //     $this->assertEquals(
    //         $expectedCode,
    //         $code
    //     );
    // }

    // public function testInspect()
    // {
    //     $reflector = Phake::mock('ReflectionFunction');
    //     $parameter1 = Phake::mock('ReflectionParameter');
    //     $parameter2 = Phake::mock('ReflectionParameter');

    //     Phake::when($reflector)
    //         ->getParameters()
    //         ->thenReturn(array($parameter1, $parameter2));

    //     Phake::when($parameter1)
    //         ->isPassedByReference()
    //         ->thenReturn(false);

    //     Phake::when($parameter2)
    //         ->isPassedByReference()
    //         ->thenReturn(true);

    //     Phake::when($parameter1)
    //         ->isOptional()
    //         ->thenReturn(false);

    //     Phake::when($parameter2)
    //         ->isOptional()
    //         ->thenReturn(true);

    //     $expected = array(
    //         1,
    //         2,
    //         array(false, true),
    //     );

    //     $this->assertSame($expected, $this->generator->inspect($reflector));

    //     Phake::inOrder(
    //         Phake::verify($reflector)->getParameters(),
    //         Phake::verify($parameter1)->isPassedByReference(),
    //         Phake::verify($parameter1)->isOptional(),
    //         Phake::verify($parameter1)->getName(),
    //         Phake::verify($parameter2)->isPassedByReference(),
    //         Phake::verify($parameter2)->isOptional(),
    //         Phake::verify($parameter2)->getName()
    //     );
    // }

    // public function testInspectEllipsis()
    // {
    //     $reflector = Phake::mock('ReflectionFunction');
    //     $parameter1 = Phake::mock('ReflectionParameter');
    //     $parameter2 = Phake::mock('ReflectionParameter');

    //     Phake::when($reflector)
    //         ->getParameters()
    //         ->thenReturn(array($parameter1, $parameter2));

    //     Phake::when($parameter1)
    //         ->isPassedByReference()
    //         ->thenReturn(false);

    //     Phake::when($parameter2)
    //         ->isPassedByReference()
    //         ->thenReturn(false);

    //     Phake::when($parameter1)
    //         ->isOptional()
    //         ->thenReturn(false);

    //     Phake::when($parameter2)
    //         ->isOptional()
    //         ->thenReturn(false);

    //     Phake::when($parameter2)
    //         ->getName()
    //         ->thenReturn('...');

    //     $expected = array(
    //         2,
    //         6,
    //         array(
    //             false,
    //             false,
    //             false,
    //             false,
    //             false,
    //             false,
    //         ),
    //     );

    //     $this->assertSame($expected, $this->generator->inspect($reflector));

    //     $parameter2Verifier = Phake::verify($parameter2, Phake::times(2));

    //     Phake::inOrder(
    //         Phake::verify($reflector)->getParameters(),
    //         Phake::verify($parameter1)->isPassedByReference(),
    //         Phake::verify($parameter1)->isOptional(),
    //         Phake::verify($parameter1)->getName(),
    //         $parameter2Verifier->isPassedByReference(),
    //         Phake::verify($parameter2)->isOptional(),
    //         Phake::verify($parameter2)->getName(),
    //         $parameter2Verifier->isPassedByReference()
    //     );
    // }

    // public function testInspectEllipsisOptional()
    // {
    //     $reflector = Phake::mock('ReflectionFunction');
    //     $parameter1 = Phake::mock('ReflectionParameter');
    //     $parameter2 = Phake::mock('ReflectionParameter');

    //     Phake::when($reflector)
    //         ->getParameters()
    //         ->thenReturn(array($parameter1, $parameter2));

    //     Phake::when($parameter1)
    //         ->isPassedByReference()
    //         ->thenReturn(false);

    //     Phake::when($parameter2)
    //         ->isPassedByReference()
    //         ->thenReturn(false);

    //     Phake::when($parameter1)
    //         ->isOptional()
    //         ->thenReturn(false);

    //     Phake::when($parameter2)
    //         ->isOptional()
    //         ->thenReturn(true);

    //     Phake::when($parameter2)
    //         ->getName()
    //         ->thenReturn('...');

    //     $expected = array(
    //         1,
    //         6,
    //         array(
    //             false,
    //             false,
    //             false,
    //             false,
    //             false,
    //             false,
    //         ),
    //     );

    //     $this->assertSame($expected, $this->generator->inspect($reflector));

    //     $parameter2Verifier = Phake::verify($parameter2, Phake::times(2));

    //     Phake::inOrder(
    //         Phake::verify($reflector)->getParameters(),
    //         Phake::verify($parameter1)->isPassedByReference(),
    //         Phake::verify($parameter1)->isOptional(),
    //         Phake::verify($parameter1)->getName(),
    //         $parameter2Verifier->isPassedByReference(),
    //         Phake::verify($parameter2)->isOptional(),
    //         Phake::verify($parameter2)->getName(),
    //         $parameter2Verifier->isPassedByReference()
    //     );
    // }

    // public function testInspectEllipsisReference()
    // {
    //     $reflector = Phake::mock('ReflectionFunction');
    //     $parameter1 = Phake::mock('ReflectionParameter');
    //     $parameter2 = Phake::mock('ReflectionParameter');

    //     Phake::when($reflector)
    //         ->getParameters()
    //         ->thenReturn(array($parameter1, $parameter2));

    //     Phake::when($parameter1)
    //         ->isPassedByReference()
    //         ->thenReturn(false);

    //     Phake::when($parameter2)
    //         ->isPassedByReference()
    //         ->thenReturn(true);

    //     Phake::when($parameter1)
    //         ->isOptional()
    //         ->thenReturn(false);

    //     Phake::when($parameter2)
    //         ->isOptional()
    //         ->thenReturn(false);

    //     Phake::when($parameter2)
    //         ->getName()
    //         ->thenReturn('...');

    //     $expected = array(
    //         2,
    //         6,
    //         array(
    //             false,
    //             true,
    //             true,
    //             true,
    //             true,
    //             true,
    //         ),
    //     );

    //     $this->assertSame($expected, $this->generator->inspect($reflector));

    //     $parameter2Verifier = Phake::verify($parameter2, Phake::times(2));

    //     Phake::inOrder(
    //         Phake::verify($reflector)->getParameters(),
    //         Phake::verify($parameter1)->isPassedByReference(),
    //         Phake::verify($parameter1)->isOptional(),
    //         Phake::verify($parameter1)->getName(),
    //         $parameter2Verifier->isPassedByReference(),
    //         Phake::verify($parameter2)->isOptional(),
    //         Phake::verify($parameter2)->getName(),
    //         $parameter2Verifier->isPassedByReference()
    //     );
    // }

    // public function testRequiresIsolatorProxyDisabled()
    // {
    //     $reflector = Phake::mock('ReflectionFunction');
    //     Phake::when($reflector)
    //         ->isDisabled()
    //         ->thenReturn(true);

    //     $this->assertFalse($this->generator->requiresIsolatorProxy($reflector));

    //     Phake::verify($reflector)->isDisabled();
    // }

    // public function testRequiresIsolatorProxyReturnsReference()
    // {
    //     $reflector = Phake::mock('ReflectionFunction');
    //     Phake::when($reflector)
    //         ->isDisabled()
    //         ->thenReturn(false);

    //     Phake::when($reflector)
    //         ->returnsReference()
    //         ->thenReturn(true);

    //     $this->assertTrue($this->generator->requiresIsolatorProxy($reflector));

    //     Phake::inOrder(
    //         Phake::verify($reflector)->isDisabled(),
    //         Phake::verify($reflector)->returnsReference()
    //     );
    // }

    // public function testRequiresIsolatorProxyReferenceParameter()
    // {
    //     $reflector = Phake::mock('ReflectionFunction');
    //     $parameter1 = Phake::mock('ReflectionParameter');
    //     $parameter2 = Phake::mock('ReflectionParameter');

    //     Phake::when($reflector)
    //         ->isDisabled()
    //         ->thenReturn(false);

    //     Phake::when($reflector)
    //         ->returnsReference()
    //         ->thenReturn(false);

    //     Phake::when($reflector)
    //         ->getParameters()
    //         ->thenReturn(array($parameter1, $parameter2));

    //     Phake::when($parameter1)
    //         ->isPassedByReference()
    //         ->thenReturn(false);

    //     Phake::when($parameter2)
    //         ->isPassedByReference()
    //         ->thenReturn(true);

    //     $this->assertTrue($this->generator->requiresIsolatorProxy($reflector));

    //     Phake::inOrder(
    //         Phake::verify($reflector)->isDisabled(),
    //         Phake::verify($reflector)->returnsReference(),
    //         Phake::verify($reflector)->getParameters(),
    //         Phake::verify($parameter1)->isPassedByReference(),
    //         Phake::verify($parameter2)->isPassedByReference()
    //     );
    // }

    // public function testRequiresIsolatorProxyNoReferenceParameters()
    // {
    //     $reflector = Phake::mock('ReflectionFunction');
    //     $parameter1 = Phake::mock('ReflectionParameter');
    //     $parameter2 = Phake::mock('ReflectionParameter');

    //     Phake::when($reflector)
    //         ->isDisabled()
    //         ->thenReturn(false);

    //     Phake::when($reflector)
    //         ->returnsReference()
    //         ->thenReturn(false);

    //     Phake::when($reflector)
    //         ->getParameters()
    //         ->thenReturn(array($parameter1, $parameter2));

    //     Phake::when($parameter1)
    //         ->isPassedByReference()
    //         ->thenReturn(false);

    //     Phake::when($parameter2)
    //         ->isPassedByReference()
    //         ->thenReturn(false);

    //     $this->assertFalse($this->generator->requiresIsolatorProxy($reflector));

    //     Phake::inOrder(
    //         Phake::verify($reflector)->isDisabled(),
    //         Phake::verify($reflector)->returnsReference(),
    //         Phake::verify($reflector)->getParameters(),
    //         Phake::verify($parameter1)->isPassedByReference(),
    //         Phake::verify($parameter2)->isPassedByReference()
    //     );
    // }
}
