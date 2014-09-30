<?php

namespace Qissues\Domain\Model\Response;

use Qissues\Domain\Model\Issue;

/**
 * Represents a collection of Issue instances
 */
class Issues implements \Countable, \IteratorAggregate, \ArrayAccess
{
    /**
     * @var Issue[]
     */
    protected $results;

    /**
     * Constructs a new instance
     * @param Issue[] $issues
     */
    public function __construct(array $issues)
    {
        foreach ($issues as $issue) {
            if (!($issue instanceof Issue)) {
                throw new \InvalidArgumentException('Issues only accepts valid Issue instances');
            }
        }

        $this->results = $issues;
    }

    /**
     * Return a new instance with issues filtered by $cb
     *
     * @param Callable $cb
     * @return Issues
     */
    public function filter($cb)
    {
        return new self(array_filter($this->results, $cb));
    }

    /**
     * Return a new instance with issues sorted by $cb
     *
     * @param Callable $cb
     * @return Issues
     */
    public function sort($cb)
    {
        $localIssues = $this->results;

        // https://bugs.php.net/bug.php?id=50688
        // throwing an exception within sort causes a PHP warning
        @usort($localIssues, $cb);

        return new self($localIssues);
    }

    /**
     * Implements \Countable
     * @return integer count
     */
    public function count()
    {
        return count($this->results);
    }

    public function map(callable $func)
    {
        return array_map($func, $this->results);
    }

    /**
     * Implements \IteratorAggregate
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->results);
    }

    /**
     * Implements \ArrayAccess
     * @param integer $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->results[$offset]);
    }

    /**
     * Implements \ArrayAccess
     * @param integer $offset
     * @return Issue
     */
    public function offsetGet($offset)
    {
        return $this->results[$offset];
    }

    /**
     * Implements \ArrayAccess
     * @throws \BadMethodCallException
     */
    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException('Issues is immutable');
    }

    /**
     * Implements \ArrayAccess
     * @throws \BadMethodCallException
     */
    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException('Issues is immutable');
    }
}
