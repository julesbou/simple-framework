<?php

namespace SF;

/*
 * This file is part of the SimpleFrameworke
 *
 * (c) Jules Boussekeyt <jules.boussekeyt@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class ClassLoader
{
    private $classPaths;

    public function __construct(array $classPaths = array(), $prepend = false)
    {
        $this->classPaths = $classPaths;

        spl_autoload_register(array($this, 'loadClass'), true, $prepend);
    }

    public function loadClass($class)
    {
        $isNamespaced = (false !== $pos = strrpos($class, '\\'));

        // namespaced class name
        $namespace = substr($class, 0, $pos);
        foreach ($this->classPaths as $ns => $dir) {
            if (0 !== strpos($class, $ns)) {
                continue;
            }

            $className = substr($class, $pos + 1);
            $file = $isNamespaced
                ? $dir.DIRECTORY_SEPARATOR.str_replace('\\', DIRECTORY_SEPARATOR, $namespace).DIRECTORY_SEPARATOR.str_replace('_', DIRECTORY_SEPARATOR, $className).'.php'
                : $dir.DIRECTORY_SEPARATOR.str_replace('_', DIRECTORY_SEPARATOR, $class).'.php';

            if (file_exists($file)) {
                require $file;
            }
        }
    }
}
