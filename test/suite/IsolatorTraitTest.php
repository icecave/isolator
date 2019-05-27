<?php
namespace Icecave\Isolator;

use PHPUnit\Framework\TestCase;
use Phake;

/**
 * @requires PHP 5.4
 * @covers Icecave\Isolator\IsolatorTrait
 */
class IsolatorTraitTest extends TestCase
{
    public function testIsolator()
    {
        $object = new TraitUsage();

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
