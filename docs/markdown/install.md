# Installation

## Create the project structure

```bash
$ mkdir simple-framework
$ cd simple-framework

$ git init
$ git submodule add git@github.com:gordonslondon/SimpleFramework.git

$ cp SimpleFramework/Kernel.php.sample Kernel.php
$ mkdir templates
$ touch log.txt
```

## Create the index file

Create an `index.php` file:

```php
<?php

require __DIR__.'/SimpleFramework/autoload.php';
require __DIR__.'/Kernel.php';
require __DIR__.'/Controller.php';

$kernel = new Kernel('DEV');
echo $kernel->run();
```

## Create the Controller

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

## Create the template file

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
