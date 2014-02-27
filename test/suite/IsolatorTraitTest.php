<?php
namespace Icecave\Isolator;

use Phake;
use PHPUnit_Framework_TestCase;

/**
 * @requires PHP 5.4
 * @covers Icecave\Isolator\IsolatorTrait
 */
class IsolatorTraitTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
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
