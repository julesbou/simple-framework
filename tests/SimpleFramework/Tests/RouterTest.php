<?php

namespace SimpleFramework\Tests;

use SimpleFramework\Router;

class RouterTest extends \PHPUnit_Framework_TestCase
{
    private $router;

    public function setUp()
    {
        $this->router = new Router(require __DIR__.'/Fixtures/routes.php');
    }

    public function testConstruct()
    {
        $this->assertAttributeEquals(require __DIR__.'/Fixtures/routes.php', 'routes', $this->router);
    }

    public function testMatch()
    {
        // test route
        $_SERVER['REQUEST_METHOD'] = 'GET';
        list($route, $params) = $this->router->match('/');
        $this->assertEquals('index', $route['name']);
        $this->assertSame(array(), $params);

        // test route
        $_SERVER['REQUEST_METHOD'] = 'POST';
        list($route, $params) = $this->router->match('/edit/9999');
        $this->assertEquals('edit', $route['name']);
        $this->assertSame(array('id' => '9999'), $params);
    }

    public function testNotFound()
    {
        try {
            $_SERVER['REQUEST_METHOD'] = 'GET';
            $this->router->match('/unknown');
            $this->fail('expected HttpException');
        } catch (\SimpleFramework\HttpException $e) {
            $this->assertEquals(404, $e->getCode());
        }
    }

    public function testBadMethod()
    {
        try {
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $this->router->match('/url');
            $this->fail('expected HttpException');
        } catch (\SimpleFramework\HttpException $e) {
            $this->assertEquals(404, $e->getCode());
        }
    }

    public function testGenerate()
    {
        try {
            $this->router->generate('unknown');
            $this->fail('could not generate unknown route');
        } catch (\InvalidArgumentException $e) {}

        $this->assertEquals('/', $this->router->generate('index'));
        $this->assertEquals('/edit/9999', $this->router->generate('edit', array('id' => 9999)));
    }
}
