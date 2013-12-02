<?php

namespace Qissues\Domain\Shared;

class ExpectedDetails implements \ArrayAccess, \IteratorAggregate, \Countable
{
    protected $expected;
    protected $indexed;

    /**
     * @param ExpectedDetail[] $expected
     * @throws InvalidArgumentException if not as expected
     */
    public function __construct(array $expected)
    {
        foreach ($expected as $detail) {
            if (!$detail instanceof ExpectedDetail) {
                throw new \InvalidArgumentException('ExpectedDetails requires ExpectedDetail objects');
            }
            $this->indexed[$detail->getName()] = $detail;
        }

        $this->expected = $expected;
    }

    public function getDefaults()
    {
        $defaults = array();
        foreach ($this->expected as $expected) {
            $defaults[$expected->getName()] = $expected->getDefault();
        }
        return $defaults;
    }

    public function offsetExists($offset)
    {
        return isset($this->indexed[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->indexed[$offset];
    }

    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException('ExpectedDetails is immutable');
    }

    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('ExpectedDetails is immutable');
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->indexed);
    }

    public function count()
    {
        return count($this->expected);
    }
}
