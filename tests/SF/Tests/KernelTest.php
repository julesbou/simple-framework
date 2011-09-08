<?php

namespace SF\Tests;

use SF\Container;

require __DIR__.'/Fixtures/controller.php';
require __DIR__.'/Fixtures/TestKernel.php';

class KernelTest extends \PHPUnit_Framework_TestCase
{
    protected $kernel;
    protected $container;

    protected function setUp()
    {
        $this->container = new Container();
        $this->kernel = new Fixtures\TestKernel('DEV', $this->container);
        $this->kernel->run();
    }

    public function testContainer()
    {
        $this->assertInstanceOf('SF\Logger', $this->container['logger']);
        $this->assertInstanceOf('SF\Router', $this->container['router']);
        $this->assertInstanceOf('SF\Templating', $this->container['templating']);
        $this->assertInstanceOf('SF\EventDispatcher', $this->container['event_dispatcher']);
    }

    public function testSteps()
    {
        $this->assertEquals('bar', $this->container['config']);
        $this->assertEquals('bar bar', $this->container['config_']);

    }

    public function testFrozeContainer()
    {
        $this->assertTrue($this->container->isFrozen());
    }

    public function testBefore()
    {
        $container = new Container();
        $kernel = new Fixtures\TestKernel('BEFORE', $container);
        $this->assertArrayHasKey('route', $kernel->run());
    }

    public function testAfter()
    {
        $container = new Container();
        $kernel = new Fixtures\TestKernel('AFTER', $container);
        $this->assertArrayHasKey('content', $kernel->run());
    }
}
