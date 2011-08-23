<?php

require_once __DIR__.'/src/SimpleFramework/ClassLoader.php';

use SimpleFramework\ClassLoader;

$loader = new ClassLoader(array(
    'SimpleFramework' => __DIR__.'/src',
));
