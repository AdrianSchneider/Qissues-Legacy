<?php

namespace Qissues\System\DataType;

class ReadOnlyArrayAccess implements \ArrayAccess
{
    public function offsetExists($offset)
    {
        return method_exists($this, 'get' . ucfirst($offset));
    }

    public function offsetGet($offset)
    {
        if (!method_exists($this, $method = 'get' . ucfirst($offset))) {
            throw new \BadMethodCallException("Cannot get invalid offset '$offset'");
        }

        return call_user_func(array($this, $method));
    }

    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException('Issue is immutable');
    }

    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('Issue is immutable');
    }
}
