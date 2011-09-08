<?php

namespace SF\Tests\Fixtures;

class TestKernel extends \SF\Kernel
{
    protected function getRoutes()
    {
        return require __DIR__.'/routes.php';
    }

    protected function getConfig()
    {
        return require __DIR__.'/config.php';
    }

    protected function getTemplatingDirectories()
    {
        return array(
            '' => __DIR__.'/templates',
        );
    }

    protected function getTemplatingVars()
    {
        return array(
            'templating' => $this->container['templating'],
            'router' => $this->container['router'],
        );
    }

    protected function getLogFile()
    {
        return __DIR__.'/log.txt';
    }

    protected function startEvents()
    {
        $env = $this->env;

        $this->container['event_dispatcher']->listen('controller.before', function($event) use($env) {
            if ($env == 'BEFORE') {
                return $event;
            }
        });

        $this->container['event_dispatcher']->listen('controller.after', function($event) use($env) {
            if ($env == 'AFTER') {
                return $event;
            }
        });
    }
    protected function startFoo($config)
    {
        $this->container['config_'] = $config.' '.$this->container['config'];
    }

    protected function initFoo($config)
    {
        $this->container['config'] = $config;
    }
}
