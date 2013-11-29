<?php

namespace Qissues\Domain\Workflow;

use Qissues\Domain\Model\Issue;
use Qissues\Domain\Shared\Status;
use Qissues\Domain\Workflow\Transition;

class TransitionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $transition = new Transition(
            $issue = $this->getMockBuilder('Qissues\Domain\Model\Issue')->disableOriginalConstructor()->getMock(),
            $status = new Status('status')
        );

        $this->assertSame($issue, $transition->getIssue());
        $this->assertSame($status, $transition->getStatus());
        $this->assertNull($transition->getFields());
    }

    public function testSetFields()
    {
        $transition = new Transition(
            $issue = $this->getMockBuilder('Qissues\Domain\Model\Issue')->disableOriginalConstructor()->getMock(),
            $status = new Status('status')
        );

        $fields = array('a' => 'b');
        $transition->addFields($fields);

        $this->assertEquals($fields, $transition->getFields());
    }

    public function testSetFieldsTwiceThrowsException()
    {
        $transition = new Transition(
            $issue = $this->getMockBuilder('Qissues\Domain\Model\Issue')->disableOriginalConstructor()->getMock(),
            $status = new Status('status')
        );

        $this->setExpectedException('BadMethodCallException');

        $transition->addFields(array('a' => 'b'));
        $transition->addFields(array('b' => 'c'));
    }
}
