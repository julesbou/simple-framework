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

class Container extends \ArrayObject
{
    protected $elements;

    protected $isFrozen = false;

    public function freeze()
    {
        $this->isFrozen = true;
    }

    public function isFrozen()
    {
        return $this->isFrozen;
    }

    public function offsetExists($offset)
    {
        return isset($this->elements[$offset]);
    }

    public function offsetGet($offset)
    {
        if (!isset($this->elements[$offset])) {
            throw new \OutOfBoundsException("element '$offset' do not exists in container");
        }

        $element = $this->elements[$offset];

        return is_callable($element) ? call_user_func($element, $this) : $element;
    }

    public function offsetSet($offset, $value)
    {
        if ($this->isFrozen) {
            throw new \RunTimeException('container is frozen');
        }

        $this->elements[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('cannot unset elements');
    }
}
