<?php

namespace Qissues\Domain\Service;

use Qissues\Domain\Model\Number;
use Qissues\Domain\Model\Message;
use Qissues\Domain\Model\Transition;
use Qissues\Domain\Model\Request\IssueTransition;
use Qissues\Domain\Shared\Status;
use Qissues\Domain\Shared\Details;
use Qissues\Domain\Service\TransitionIssue;

class TransitionIssueTest extends \PHPUnit_Framework_TestCase
{
    public function testTransition()
    {
        $issue = new Number(1);
        $status = new Status('closed');
        $builder = function(){};
        $details = new Details(array('a' => 'b'));
        $transition = new Transition($status, $details);

        $workflow = $this->getMock('Qissues\Domain\Model\Workflow');
        $workflow
            ->expects($this->once())
            ->method('buildTransition')
            ->with($issue, $status, $builder)
            ->will($this->returnValue($transition))
        ;
        $workflow
            ->expects($this->once())
            ->method('apply')
            ->with($transition, $issue)
        ;

        $repository = $this->getMock('Qissues\Domain\Model\IssueRepository');

        $service = new TransitionIssue($workflow, $repository);
        $service(new IssueTransition($issue, $status, $builder));
    }

    public function testTransitionWithComment()
    {
        $issue = new Number(1);
        $status = new Status('closed');
        $builder = function(){};
        $details = new Details(array('a' => 'b'));
        $message = new Message('oh hai');
        $transition = new Transition($status, $details);

        $workflow = $this->getMock('Qissues\Domain\Model\Workflow');
        $workflow
            ->expects($this->once())
            ->method('buildTransition')
            ->with($issue, $status, $builder)
            ->will($this->returnValue($transition))
        ;
        $workflow
            ->expects($this->once())
            ->method('apply')
            ->with($transition, $issue)
        ;

        $repository = $this->getMock('Qissues\Domain\Model\IssueRepository');
        $repository
            ->expects($this->once())
            ->method('comment')
            ->with($issue, $message)
        ;

        $service = new TransitionIssue($workflow, $repository);
        $service(new IssueTransition($issue, $status, $builder, $message));
    }
}
