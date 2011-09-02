<?php

namespace SimpleFramework;

use SimpleFramework\Router;

class View
{
    protected $slots;

    protected $router;

    public function setRouter(Router $router)
    {
        $this->router = $router;
    }

    public function url($route, array $params = array())
    {
        return $this->router->generate($route, $params);
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
}
