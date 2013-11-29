<?php

namespace Qissues\Domain\Service;

use Qissues\Domain\Service\EditIssue;
use Qissues\Domain\Model\Number;
use Qissues\Domain\Model\Request\IssueChanges;

class EditIssueTest extends \PHPUnit_Framework_TestCase
{
    public function testEdit()
    {
        $issue = new Number(1);
        $changes = $this->getMockBuilder('Qissues\Domain\Model\Request\NewIssue')->disableOriginalConstructor()->getMock();

        $repository = $this->getMock('Qissues\Domain\Model\IssueRepository');
        $repository
            ->expects($this->once())
            ->method('update')
            ->with($changes, $issue)
        ;

        $service = new EditIssue($repository);
        $service(new IssueChanges($issue, $changes));
    }
}
