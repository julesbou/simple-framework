<?php

namespace SimpleFramework;

class View extends \ArrayObject
{
    protected $slots;
    protected $router;
    protected $templating;
    protected $helpers;

    public function setRouter(Router $router)
    {
        $this->router = $router;
    }

    public function setTemplating(Templating $templating)
    {
        $this->templating = $templating;
    }

    public function url($route, array $params = array())
    {
        return $this->router->generate($route, $params);
    }

    public function render($template, array $vars = array())
    {
        return $this->templating->render($template, $vars);
    }

    public function slot($key, $val = null)
    {
        if (null === $val) {
            return isset($this->slots[$key]) ? $this->slots[$key] : null;
        }

        $this->slots[$key] = $val;
    }

    public function slotStart()
    {
        ob_start();
    }

    public function slotStop($key)
    {
        $this->slots[$key] = ob_get_clean();
    }

    public function offsetExists($offset)
    {
        return isset($this->helpers[$offset]);
    }

    public function offsetGet($offset)
    {
        if (!isset($this->helpers[$offset])) {
            throw new \OutOfBoundsException("helper '$offset' do not exists");
        }

        return $this->helpers[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->helpers[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('cannot unset elements');
    }
}
