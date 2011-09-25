## Kernel

The kernel is the main part of the framework, it is responsible for:

- build container
- choosing a route
- call some events (`controller.before` and `controller.after`)
- return response html

You can not use `SF\Kernel` directly, you have to extend it and declare some functions:

- `getRoutes()` : return an array of routes
- `getTemplatingDirectories()` : return an array of directories to find templates
- `getTemplatingVars()` : return an array of variables that get passed to the view
- `getLogFile()` : return the filename of the log file

For exemple:

```php

<?php

class MyKernel extends \SF\Kernel
{
    protected function getRoutes()
    {
        return require __DIR__.'/routes.php';
    }

    protected function getConfig();
    {
        return array('foo' => 'bar');
    }

    protected function getTemplatingDirectories()
    {
        return array('' => __DIR__.'/app/templates');
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
```

You can now instanciate the kernel like this:

```php
<?php

$kernel = new MyKernel('DEV');
```
