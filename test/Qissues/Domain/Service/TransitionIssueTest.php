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
        $transition = new Transition(new Status('open'), new Details);

        $workflow = $this->getMock('Qissues\Domain\Model\Workflow');
        $workflow
            ->expects($this->once())
            ->method('apply')
            ->with($transition, $issue)
        ;

        $repository = $this->getMock('Qissues\Domain\Model\IssueRepository');

        $service = new TransitionIssue($workflow, $repository);
        $service(new IssueTransition($issue, $transition));
    }

    public function testTransitionWithComment()
    {
        $issue = new Number(1);
        $transition = new Transition(new Status('open'), new Details);
        $message = new Message('new message');

        $workflow = $this->getMock('Qissues\Domain\Model\Workflow');
        $workflow
            ->expects($this->once())
            ->method('apply')
            ->with($transition, $issue)
        ;

        $repository = $this->getMock('Qissues\Domain\Model\IssueRepository');

        $service = new TransitionIssue($workflow, $repository);
        $service(new IssueTransition($issue, $transition, $message));
    }
}
