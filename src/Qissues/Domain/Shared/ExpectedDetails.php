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

    /**
     * Returns key/value pairs of the default values
     *
     * @return array [fieldName => default value]
     */
    public function getDefaults()
    {
        $defaults = array();
        foreach ($this->expected as $expected) {
            $defaults[$expected->getName()] = $expected->getDefault();
        }
        return $defaults;
    }

    /**
     * Implements \ArrayAccess
     *
     * @param string $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->indexed[$offset]);
    }

    /**
     * Implements \ArrayAccess
     *
     * @param string $offset
     * @return ExpectedDetail
     */
    public function offsetGet($offset)
    {
        return $this->indexed[$offset];
    }

    /**
     * Implements \ArrayAccess
     *
     * @param string $offset
     * @param mixed $value
     * @throws \BadMethodCallException always
     */
    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException('ExpectedDetails is immutable');
    }

    /**
     * Implements \ArrayAccess
     *
     * @param string $offset
     * @throws \BadMethodCallException always
     */
    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('ExpectedDetails is immutable');
    }

    /**
     * Implements /IteratorAggregate
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->indexed);
    }

    /**
     * Implements \Countable
     * @return integer number of items
     */
    public function count()
    {
        return count($this->expected);
    }
}
