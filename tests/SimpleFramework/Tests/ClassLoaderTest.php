<?php

namespace SimpleFramework\Tests;

use SimpleFramework\ClassLoader;

class ClassLoaderTest extends \PHPUnit_Framework_TestCase
{
    public function testRegister()
    {
        $this->assertFalse(class_exists('\\Namespaced\\NClass'));
        $this->assertFalse(class_exists('\Pear_PClass'));

        $autoloader = new ClassLoader(array(
            'Namespaced' => __DIR__.'/Fixtures/classes',
            'Pear_' => __DIR__.'/Fixtures/classes',
        ));

        $this->assertTrue(class_exists('\\Namespaced\\NClass'));
        $this->assertTrue(class_exists('\Pear_PClass'));
    }
}
