<?php

namespace SimpleFramework;

class Controller
{
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    protected function render($template, $vars = array(), $layout = null)
    {
        return $this->container['templating']->render($template, $vars, $layout);
    }

    protected function url($route, $params = array())
    {
        return $this->container['router']->generate($route, $params);
    }

    protected function newHttpException($message, $code)
    {
        if (!is_int($code)) {
            throw new \InvalidArgumentException('status code should be an integer');
        }

        return new HttpException($message, $code);
    }

    protected function redirect($url)
    {
        header('Status: 301 Moved Permanently', false, 301);
        header('Location: '.$url);
        exit();
    }
}
