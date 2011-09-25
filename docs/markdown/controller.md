## Controller

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


