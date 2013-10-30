<?php

namespace Qissues\Tests\System\Format;

use Qissues\System\DataType\ReadOnlyArrayAccess;

class ReadOnlyArrayAccessTest extends \PHPUnit_Framework_TestCase
{
    public function testOffsetExistsReturnsTrueWhenExists()
    {
        $example = new Example();
        $this->assertTrue(isset($example['value']));
    }

    public function testOffsetExistsReturnsFalseOtherwise()
    {
        $example = new Example();
        $this->assertFalse(isset($example['wat']));
    }

    public function testOffsetGetReturnsGetter()
    {
        $example = new Example();
        $example->setValue('a');
        $this->assertEquals('a', $example['value']);
    }

    public function testOffsetGetThrowsExceptionOnInvalidField()
    {
        $this->setExpectedException('BadMethodCallException');
        $example = new Example();
        $example['error'];
    }

    public function testOffsetSetThrowsException()
    {
        $this->setExpectedException('BadMethodCallException', 'immutable');
        $example = new Example();
        $example['value'] = 'anything';
    }

    public function testOffsetUnsetThrowsException()
    {
        $this->setExpectedException('BadMethodCallException', 'immutable');
        $example = new Example();
        unset($example['value']);
    }
}

class Example extends ReadOnlyArrayAccess
{
    protected $value;

    public function setValue($val)
    {
        $this->value = $val;
    }

    public function getValue()
    {
        return $this->value;
    }
}
