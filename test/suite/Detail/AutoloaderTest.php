<?php
namespace Icecave\Isolator\Detail;

use Exception;
use PHPUnit_Framework_TestCase;
use Phake;

class AutoloaderTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->path          = tempnam(sys_get_temp_dir(), 'isolator-');
        $this->codeGenerator = Phake::mock(__NAMESPACE__ . '\CodeGenerator');

        $this->autoloader = new Autoloader(
            $this->path,
            $this->codeGenerator
        );

        unlink($this->path);

        Phake::when($this->codeGenerator)
            ->generate(Phake::anyParameters())
            ->thenReturn('<?php echo "Included!";');
    }

    public function testLoad()
    {
        $this->expectOutputString('Included!');

        $this->autoloader->load('Foo');

        $functions = get_defined_functions();

        Phake::verify($this->codeGenerator)->generate(
            'Foo',
            $functions['internal']
        );

        // Additional load should not produce additional output ...
        $this->autoloader->load('Foo');

        Phake::verify($this->codeGenerator, Phake::times(1))->generate(Phake::anyParameters());
    }

    public function testDirectoryIsCreatedWorldWritable()
    {
        $this->expectOutputString('Included!');

        $this->autoloader->load('Foo');

        $this->assertEquals(
            0777,
            stat($this->path)['mode'] & 0777,
            'Isolator temporary directory must be world writable.'
        );
    }

    public function testUmaskIsReset()
    {
        $umask = umask();

        $this->expectOutputString('Included!');

        $this->autoloader->load('Foo');

        $this->assertEquals(
            $umask,
            umask()
        );
    }

    public function testUmaskIsResetAfterException()
    {
        $umask     = umask();
        $exception = new Exception('The exception!');

        Phake::when($this->codeGenerator)
            ->generate(Phake::anyParameters())
            ->thenThrow($exception);

        $this->setExpectedException(
            Exception::class,
            'The exception!'
        );

        try {
            $this->autoloader->load('Foo');
        } catch (Exception $e) {
            $this->assertEquals(
                $umask,
                umask()
            );

            throw $e;
        }
    }
}
