<?php

namespace Qissues\Domain\Shared;

use Qissues\Domain\Shared\ExpectedDetails;

class ExpectedDetailsTest extends \PHPUnit_Framework_TestCase
{
    public function testThrowsExceptionIfNotExpectedDetail()
    {
        $this->setExpectedException('InvalidArgumentException');
        $details = new ExpectedDetails(array('field'));
    }

    public function testGetDefaults()
    {
        $details = new ExpectedDetails( array($detail = new ExpectedDetail('name', true, 'adrian')));
        $this->assertEquals(array('name' => 'adrian'), $details->getDefaults());
    }

    public function testOffsetExists()
    {
        $details = new ExpectedDetails( array($detail = new ExpectedDetail('name')));
        $this->assertTrue(isset($details['name']));
    }

    public function testOffsetGet()
    {
        $details = new ExpectedDetails( array($detail = new ExpectedDetail('name')));
        $this->assertSame($detail, $details['name']);
    }

    public function testOffsetSetThrowsException()
    {
        $this->setExpectedException('BadMethodCallException', 'immutable');
        $details = new ExpectedDetails( array($detail = new ExpectedDetail('name')));
        $details['name'] = 'pizza';
    }

    public function testOffsetUnsetThrowsException()
    {
        $this->setExpectedException('BadMethodCallException', 'immutable');
        $details = new ExpectedDetails( array($detail = new ExpectedDetail('name')));
        unset($details['name']);
    }

    public function testIsIteratable()
    {
        $details = new ExpectedDetails( array($detail = new ExpectedDetail('name')));
        foreach ($details as $name => $deet) {
            $this->assertEquals('name', $name);
            $this->assertSame($detail, $deet);
        }
    }
}
