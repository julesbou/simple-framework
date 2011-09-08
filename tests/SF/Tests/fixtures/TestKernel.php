<?php

namespace SF\Tests\Fixtures;

class Kernel extends \SF\Kernel
{
    protected function getRoutes()
    {
        return require __DIR__.'/routes.php';
    }

    protected function getConfig();
    {
        return require __DIR__.'/config.php';
    }

    protected function getTemplatingDirectories()
    {
        return array(
            'namespace' => __DIR__.'/app/templates',
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
}
