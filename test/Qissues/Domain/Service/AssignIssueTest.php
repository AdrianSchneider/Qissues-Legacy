<?php

namespace Qissues\Domain\Service;

use Qissues\Domain\Model\Number;
use Qissues\Domain\Model\Message;
use Qissues\Domain\Shared\User;
use Qissues\Domain\Service\AssignIssue;
use Qissues\Domain\Model\Request\IssueAssignment;

class AssignIssueTest extends \PHPUnit_Framework_TestCase
{
    public function testAssign()
    {
        $assignment = new IssueAssignment(
            $issue = new Number(1),
            $assignee = new User('myself')
        );

        $repository = $this->getMock('Qissues\Domain\Model\IssueRepository');
        $repository
            ->expects($this->once())
            ->method('assign')
            ->with($issue, $assignee)
        ;

        $service = new AssignIssue($repository);
        $service($assignment);
    }

    public function testAssignAndComment()
    {
        $assignment = new IssueAssignment(
            $issue = new Number(1),
            $assignee = new User('myself'),
            $comment = new Message('sup')
        );

        $repository = $this->getMock('Qissues\Domain\Model\IssueRepository');
        $repository
            ->expects($this->once())
            ->method('assign')
            ->with($issue, $assignee)
        ;
        $repository
            ->expects($this->once())
            ->method('comment')
            ->with($issue, $comment)
        ;

        $service = new AssignIssue($repository);
        $service($assignment);
    }
}
