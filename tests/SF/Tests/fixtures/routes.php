<?php

return array(
    'index' => array(
        'pattern' => '/',
        'controller' => 'controller',
        'action' => 'index',
    ),
    'url' => array(
        'pattern' => '/url',
        'controller' => 'controller',
        'action' => 'badAction',
        'method' => 'GET',
    ),
    'edit' => array(
        'pattern' => '/edit/{id}',
        'controller' => 'controller',
        'action' => 'edit',
        'method' => 'POST',
    ),
);
