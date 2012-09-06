
# Simple Framework

PHP framework working with PHP5.3 who wants to be simple.

It only contains minimum viable features to make a web framework.

## Installation

### Create the project structure

```bash
$ mkdir simple-framework
$ cd simple-framework

$ git init
$ git submodule add git@github.com:julesbou/SimpleFramework.git

$ cp SimpleFramework/Kernel.php.sample Kernel.php
$ mkdir templates
$ touch log.txt
```

### Create the index file

Create an `index.php` file:

```php
<?php

require __DIR__.'/SimpleFramework/autoload.php';
require __DIR__.'/Kernel.php';
require __DIR__.'/Controller.php';

$kernel = new Kernel('DEV');
echo $kernel->run();
```

### Create the Controller

Create a `Controller.php` file:

```php
<?php

class Controller extends SF\Controller
{
    public function indexAction()
    {
        return $this->render('index.php', array('message' => 'Hello'));
    }
}
```

### Create the template file

Create a file under `templates/index.php`:

```html
<!DOCTYPE html>
<html>
    <body>
        <?php echo $message ?>!
    </body>
</html>
```

Your done! Go to your web browser to `http://yoursite.com/index.php`.




## Internals

### Container

Container let you manage dependencies over your project. With it you can register services (a php class) and inject them into other services. No use of singletons.

The container has 2 states:

- `open` You can register new services in it.
- `frozen` You can __not__ register new services (it means that the `Container::freeze()` method has been called).

Register something in it:

```php
<?php

$container = new SimpleFramework\Container();

// register a parameter
$container['parameter_name'] = 'parameter_value';

// register a service
$container['user_provider'] = new UserProvider();

// lazy register a service
$container['auth'] = function($container) 
    return new Auth($container['user_provider']);
};
```

Then freeze the container, it means you can not register services in it anymore:

```php
<?php
$container->freeze();
```

Get a service in the container:

```php
<?php
$auth = $container['auth'];
```

Inside each controller you have access to the container:

```php
<?php

namespace MyApp\Controller;

class UserController extends \SimpleFramework\Controller
{
    public function indexAction()
    {
        $service = $this->container['service_name'];
    }
}
```


### Controller

A controller can be a POPO (Plain old PHP object) or extends Controller, in the second case you have shortcut methods inside the `SimpleFramework\Controller` class.

The `Container` is passed to the controller in the first argument of his `__construct()` method.

Usage:

```php
<?php

namespace MyApp\Controller;

class UserController extends \SimpleFramework\Controller
{
    public function indexAction()
    {
        $url = $this->url('index');
        //$url = $this->container['router']->generate('index');

        return $this->render('index.php');
        // return $this->container['templating']->render('index.php');
    }
}
```

### Event Dispatcher

EventDispatcher helps you to make your code more extendible, reusable and flexible. The event dispatcher register listeners (or callback) that can be called later.

A simple use case is the wish to change session data of the logged-in user when we update the database, we can achieve this with the creation of a user.onChange event and add a listener to it:

```php
<?php

use SimpleFramework\EventDispatcher;

$dispatcher = new EventDispatcher();

// register the listener
$dispatcher->listen('user.onChange', function($event) {
    // refresh user session data
});

// call the listener
$dispatcher->dispatch('user.onChange');
```

SimpleFramework comes with 2 core events that let you return a response:

- `controller.before` called before the action
- `controller.after` called after the action

A simple use case is the wish to add a web-debug-toolbar:

```php
<?php

use SimpleFramework\Container;
use SimpleFramework\Kernel;

$container= new Container();
$container['env'] = 'DEV';
$kernel = new Kernel($container);

$container['event_dispatcher']->listen('controller.after', function($event) use($container) {
    $content = $event['content'];

    if ('DEV' === $container['env']) {
        // rendering the toolbar html
        $toolbarHTML = $container['templating']->render('toolbar.php');
        // append the toolbar to the html
        return $content.$toolbarHTML;
     }
});

$kernel->run();
```

### Kernel

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

### Router

Router match an url to find a route, usage:

```php
<?php

// define some route
$routes = array(
    // minimal config
    'index' => array(
        'pattern'           => '/',
        'controller'        => 'MyNamespace\Controller',
        'action'            => 'edit',
    ),
    // full config
    'edit' => array(
        'pattern'           => '/edit/{id}-{slug}',
        'controller'        => 'MyNamespace\Controller',
        'action'            => 'edit',
        'method'            => 'POST',
        'requirements'      => array('id' => '[0-9]+'),
    ),
);

$router = new SF\Router($routes);

// match a simple route
list($route, $params) = $router->match('/');

// match a route with placeholders
list($route, $params) = $router->match('/edit/9999-slug');
echo $params['id']; // 9999
echo $params['slug']; // slug

// route not found
try {
    $router->match('/unknown');
} catch (SF\HttpException $e) {
    echo $e->getCode(); // 404
}

// generate url
echo $router->generate('index');
echo $router->generate('edit', array('id' => 9999, 'slug' => 'slug'));
```

The controller look like this:

```php
<?php

namespace MyNamespace;

class Controller
{
    public function indexAction()
    {
        return 'index';
    }

    public function editAction($id, $slug)
    {
        return "editing $slug";
    }
}
```

### Templating


Render a html template, pass variables to it, decorate it with a layout, usage:

```php
<?php

// keys are namespaces
$templating = new SimpleFramework\Templating(array(
    ''    => __DIR__.'/templates',
    'baz' => __DIR__.'/apps/baz/templates',
));

// render a template and pass variable to it
$templating->render('foo.php', array('bar' => 'baz'));

// render a template and give it a layout
$templating->render('foo.php', array('bar' => 'baz'), 'layout.php');

// with a namespace (/apps/baz/templates/index.php)
$templating->render('baz:index.php');
```

`/templates/foo.php` look like this:

```php
<?php echo $bar ?>
```

`/templates/layout.php` look like this:

```html
<html>
  <body>
    <div class="page">
      <h1>MyWebsite</h2>
      <?php echo $content; ?>
    </div>
  </body>
</html>
```


## Contributing

Run tests: `phpunit`
