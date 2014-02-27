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
    public function testIsolator()
    {
        $object = new TraitUsage;

        $instance = Isolator::get();

        $this->assertSame($instance, $object->isolator());

        $instance = Phake::mock(__NAMESPACE__ . '\Isolator');

        $object->setIsolator($instance);

        $this->assertSame($instance, $object->isolator());

        $object->setIsolator(null);

        $instance = Isolator::get();

        $this->assertSame($instance, $object->isolator());
    }
}
