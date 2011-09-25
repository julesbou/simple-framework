## Container

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


