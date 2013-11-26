<?php

namespace Qissues\Model\Workflow;

use Qissues\Model\Issue;
use Qissues\Model\Meta\Status;
use Qissues\Model\Workflow\Transition;

class TransitionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $transition = new Transition(
            $issue = $this->getMockBuilder('Qissues\Model\Issue')->disableOriginalConstructor()->getMock(),
            $status = new Status('status')
        );

        $this->assertSame($issue, $transition->getIssue());
        $this->assertSame($status, $transition->getStatus());
        $this->assertNull($transition->getFields());
    }

    public function testSetFields()
    {
        $transition = new Transition(
            $issue = $this->getMockBuilder('Qissues\Model\Issue')->disableOriginalConstructor()->getMock(),
            $status = new Status('status')
        );

        $fields = array('a' => 'b');
        $transition->addFields($fields);

        $this->assertEquals($fields, $transition->getFields());
    }

    public function testSetFieldsTwiceThrowsException()
    {
        $transition = new Transition(
            $issue = $this->getMockBuilder('Qissues\Model\Issue')->disableOriginalConstructor()->getMock(),
            $status = new Status('status')
        );

        $this->setExpectedException('BadMethodCallException');

        $transition->addFields(array('a' => 'b'));
        $transition->addFields(array('b' => 'c'));
    }
}
