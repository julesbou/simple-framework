<?php

namespace SimpleFramework\Tests;

use SimpleFramework\Kernel;
use SimpleFramework\Container;

require __DIR__.'/Fixtures/controller.php';

class KernelTest extends \PHPUnit_Framework_TestCase
{
    protected $kernel;
    protected $container;

    public function setUp()
    {
        $container = new Container();
        $container['logger.file'] = __DIR__.'/Fixtures/log.txt';
        $container['router.routes'] = require __DIR__.'/Fixtures/routes.php';
        $container['templating.directories'] = array(
            '' => __DIR__.'/Fixtures/templates'
        );

        $this->container = $container;
        $this->kernel = new Kernel($container);
    }

    public function testConstruct()
    {
        $this->assertTrue(isset($this->container['router']));
        $this->assertTrue(isset($this->container['logger']));
        $this->assertTrue(isset($this->container['templating']));
        $this->assertTrue(isset($this->container['event_dispatcher']));

        $_SERVER['PATH_INFO'] = '/';
        $this->kernel->run();
        $this->assertTrue($this->container->isFrozen());
    }


    public function testRun()
    {
        $_SERVER['PATH_INFO'] = '/';
        $this->assertEquals("template content\n", $this->kernel->run());

        $_SERVER['PATH_INFO'] = '/edit/blabla';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->assertEquals("blabla", $this->kernel->run());
    }

    public function testOverrideTemplateParameters()
    {
        $this->container['templating.vars'] = array('var' => 'global var');
        $kernel = new Kernel($this->container);

        $this->assertEquals("global var", $this->container['templating']->render('var_template.php'));
    }

    public function testInvalidConfig()
    {
        try {
            $container = new Container();
            $container['templating.directories'] = array();
            $kernel = new Kernel($container);
            $this->fail('should provide templating directories');
        } catch (\ErrorException $e) {}

        try {
            $container = new Container();
            $container['router.routes'] = array();
            $kernel = new Kernel($container);
            $this->fail('should provide routes');
        } catch (\ErrorException $e) {}
    }

    public function testActionNotExists()
    {
        try {
            $_SERVER['PATH_INFO'] = '/url';
            $_SERVER['REQUEST_METHOD'] = 'GET';
            $this->kernel->run();
            $this->fail('exception should be thrown if action do not exists');
        } catch (\InvalidArgumentException $e) {}
    }

    public function testBefore()
    {
        $phpunit = $this;
        $this->container['event_dispatcher']->listen('controller.before', function($event) use($phpunit) {
            $phpunit->assertArrayHasKey('route', $event);
            return 'before';
        });
        $_SERVER['PATH_INFO'] = '/';
        $this->assertEquals("before", $this->kernel->run());
    }

    public function testAfter()
    {
        $phpunit = $this;
        $this->container['event_dispatcher']->listen('controller.after', function($event) use($phpunit) {
            $phpunit->assertArrayHasKey('content', $event);
            return $event['content'].' added content';
        });
        $_SERVER['PATH_INFO'] = '/';
        $this->assertEquals("template content\n added content", $this->kernel->run());
    }
}
