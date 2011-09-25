## Templating

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
