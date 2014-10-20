<?php
namespace Icecave\Isolator\Detail;

use PHPUnit_Framework_TestCase;

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
        $expectedCode .= '        switch (\func_num_args()) {' . PHP_EOL;
        $expectedCode .= '            case 1: return \strlen($p0);' . PHP_EOL;
        $expectedCode .= '        }' . PHP_EOL;
        $expectedCode .= PHP_EOL;
        $expectedCode .= '        $arguments = \func_get_args();' . PHP_EOL;
        $expectedCode .= PHP_EOL;
        $expectedCode .= '        return \call_user_func_array(\'strlen\', $arguments);' . PHP_EOL;
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
        $expectedCode .= '        switch (\func_num_args()) {' . PHP_EOL;
        $expectedCode .= '            case 2: return \ereg($p0, $p1);' . PHP_EOL;
        $expectedCode .= '            case 3: return \ereg($p0, $p1, $p2);' . PHP_EOL;
        $expectedCode .= '        }' . PHP_EOL;
        $expectedCode .= PHP_EOL;
        $expectedCode .= '        $arguments = \func_get_args();' . PHP_EOL;
        $expectedCode .= '        $arguments[2] = &$p2;' . PHP_EOL;
        $expectedCode .= PHP_EOL;
        $expectedCode .= '        return \call_user_func_array(\'ereg\', $arguments);' . PHP_EOL;
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
        $expectedCode .= '        switch (\func_num_args()) {' . PHP_EOL;
        $expectedCode .= '            case 2: return \sprintf($p0, $p1);' . PHP_EOL;
        $expectedCode .= '        }' . PHP_EOL;
        $expectedCode .= PHP_EOL;
        $expectedCode .= '        $arguments = \func_get_args();' . PHP_EOL;
        $expectedCode .= PHP_EOL;
        $expectedCode .= '        return \call_user_func_array(\'sprintf\', $arguments);' . PHP_EOL;
        $expectedCode .= '    }' . PHP_EOL;
        $expectedCode .= '}' . PHP_EOL;

        $this->assertSame(
            $expectedCode,
            $code
        );
    }
}
