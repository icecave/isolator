<?php
namespace Icecave\Isolator;

use Icecave\Isolator\TestFixture\TraitUsage;
use Phake;
use PHPUnit_Framework_TestCase;

/**
 * @covers Icecave\Isolator\IsolatorTrait
 */
class IsolatorTraitTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        if (version_compare(PHP_VERSION, '5.4', '<')) {
            $this->markTestSkipped('This test requires PHP v5.4 or above.');
        }

        $this->object = new TraitUsage;
    }

    public function testIsolator()
    {
        $instance = Isolator::get();

        $this->assertSame($instance, $this->object->isolator());

        $instance = Phake::mock(__NAMESPACE__ . '\Isolator');

        $this->object->setIsolator($instance);

        $this->assertSame($instance, $this->object->isolator());

        $this->object->setIsolator(null);

        $instance = Isolator::get();

        $this->assertSame($instance, $this->object->isolator());
    }
}
