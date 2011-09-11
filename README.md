# SimpleFramework - small bootstrap for your next PHP application.

SimpleFramework is a web framework working with PHP5.3.

SimpleFramework want to be simple, only the minimum viable feature to make a framework, it will never contains features like forms, orm, translation, etc.

## Autoloader

Autoloader is a lazy class loader, it works with prefixes and namespaces. Usage:

```php
<?php

require __DIR__.'/path/to/simple-framework/src/SF/Autoloader.php';

use SF\Autoloader;

$loader = new Autoloader(array(
    'Pear_'         => __DIR__.'/libs',
    'Namespace'     => __DIR__.'/libs',
));
$loader->register();
```

## Container

Container let you manage dependencies over your project. With it you can register services (a php class) and inject them into other services. No use of singletons.

The container has 2 states:

- `open` You can register new services in it.
- `frozen` You can __not__ register new services (it means that the `Kernel::run()` method has been called).

Usage:

```php
<?php

use SF\Container;

$container = new Container();

// register a parameter
$container['parameter_name'] = 'parameter_value';

// register a service
$container['user_provider'] = new UserProvider();

// lazy register a service
$container['auth'] = function($container) 
    return new Auth($container['user_provider']);
};

// then freeze your container
$container->freeze();

// get an element of a container
$auth = $container['auth'];

// cuz container is frozen you cannot add new services
try {
    $container['new_service'] = new Service();
} catch (\RunTimeException $e) {}
```

Inside each controller you have access to the container:

```php
<?php

namespace MyApp\Controller;

class UserController extends \SF\Controller
{
    public function indexAction()
    {
        $service = $this->container['service_name'];
    }
}
```

## Controller

A controller can be a POPO (Plain old PHP object) or extends Controller, in the second case you have shortcut methods inside the `SF\Controller` class.

The container is passed to the controller in the first argument of his `__construct()` method.

Usage:

```php
<?php

namespace MyApp\Controller;

class UserController extends \SF\Controller
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

## EventDispatcher

EventDispatcher helps you to make your code more extendible, reusable and flexible. The event dispatcher register listeners (or callback) that can be called later.

A simple use case is the wish to change session data of the logged-in user when we update the database, we can achieve this with the creation of a user.onChange event and add a listener to it:

```php
<?php

use SF\EventDispatcher;

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

use SF\Container;
use SF\Kernel;

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

## Kernel

The kernel is the main part of the framework, it is responsible for:

- choosing a route
- call controller
- call some events (`controller.before` and `controller.after`)
- return response html

Usage:

```php
<?php

use SF\Container;
use SF\Kernel;

$container = new Container();
$container['router.routes'] = array();
$container['templating.directories'] = array();

$kernel = new Kernel($container);
echo $kernel->run();
```

## Logger

Usage:

```php
<?php

use SF\Logger;

$logger = new Logger(__DIR__.'/logs.log');

$logger->log('message'); // DEBUG
$logger->log('message', Logger::DEBUG);
$logger->log('message', Logger::WARNING);
$logger->log('message', Logger::ERROR);
$logger->log('message', Logger::CRITICAL);
$logger->log('message', 'emergency'); // custom type
```

## Router

Router match an url to find a route, usage:

```php
<?php

// define some route
$routes = array(
    // minimal config
    array(
        'name'              => 'index',
        'pattern'           => '/',
    ),
    // full config
    array(
        'name'              => 'edit',
        'pattern'           => '/edit/{id}',
        'controller'        => 'Namespace\Controller',
        'action'            => 'edit',
        'method'            => 'POST',
    ),
);

list($route, $params) = $router->match('/');
echo $route['name']; // index

list($route, $params) = $router->match('/edit/9999');
echo $route['name']; // edit
echo $params['id']; // 9999

try {
    // route not found
    $router->match('/unknown');
} catch (\InvalidArgumentException $e) {}

echo $router->generate('index'); // /
echo $router->generate('edit', array('id' => 9999)); // /edit/9999
```

## Templating

Render a html template, pass variables to it, decorate it with a layout, usage:

```php
<?php

$templating = new SF\Templating(array(
    ''    => __DIR__.'/templates',
    'baz' => __DIR__.'/apps/baz/templates',
));

// render a template (/templates/main.php)
$templating->render('main.php');
 
// render a template in a namespace (/apps/baz/templates/index.php)
$templating->render('baz:index.php');

// render a template and pass variable to it
$templating->render('baz:foo.php', array('bar' => 'baz'));

// render a template and give it a layout
$templating->render('baz:index.php', array(), 'layout.php');
```

`/apps/baz/templates/foo.php` look like this:

```html
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
