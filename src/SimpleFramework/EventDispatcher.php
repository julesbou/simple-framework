<?php

namespace SimpleFramework;

class EventDispatcher
{
    private $listeners;

    public function listen($event, $callback)
    {
        $this->listeners[$event][] = $callback;
    }

    public function dispatch($event, $params = null)
    {
        if (!isset($this->listeners[$event])) {
            return;
        }

        foreach ($this->listeners[$event] as $listener) {
            if (null !== ($result = call_user_func($listener, $params))) {
                return $result;
            }
        }
    }
}
