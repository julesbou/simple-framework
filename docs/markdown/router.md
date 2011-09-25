## Router

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
