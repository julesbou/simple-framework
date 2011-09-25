## EventDispatcher

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


