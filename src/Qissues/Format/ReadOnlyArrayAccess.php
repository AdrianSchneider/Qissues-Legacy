<?php

namespace Qissues\Format;

class ReadOnlyArrayAccess implements \ArrayAccess
{
    public function offsetExists($offset)
    {
        return method_exists($this, 'get' . ucfirst($offset));
    }

    public function offsetGet($offset)
    {
        return call_user_func(array($this, 'get' . ucfirst($offset)));
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
