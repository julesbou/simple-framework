<?php

namespace SF\Tests;

use SF\Container;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testFreeze()
    {
        $container = new Container();
        $this->assertFalse($container->isFrozen());
        $container->freeze();
        $this->assertTrue($container->isFrozen());
    }

    public function testExists()
    {
        $container = new Container();
        $this->assertFalse(isset($container['element']));
        $container['element'] = 'value';
        $this->assertTrue(isset($container['element']));
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testGetUnknownElement()
    {
        $container = new Container();
        $container['unknown'];
    }

    public function testGetCallable()
    {
        $container = new Container();
        $phpunit = $this;
        $container['callable'] = function($container) use($phpunit) {
            $phpunit->assertInstanceof('SF\Container', $container);
            return 'called';
        };
        $this->assertEquals('called', $container['callable']);
    }

    public function testGetElement()
    {
        $container = new Container();
        $container['element'] = 'value';
        $this->assertEquals('value', $container['element']);
    }

    public function testSet()
    {
        $container = new Container();
        $container['element'] = 'value';
        $this->assertEquals('value', $container['element']);
    }

    /**
     * @expectedException RunTimeException
     */
    public function testSetWithFrozenContainer()
    {
        $container = new Container();
        $container->freeze();
        $container['element'] = 'value';
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testCannotUnset()
    {
        $container = new Container();
        unset($container['element']);
    }
}
