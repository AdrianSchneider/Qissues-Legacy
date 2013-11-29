<?php

namespace Qissues\Domain\Service;

use Qissues\Domain\Service\CreateIssue;

class CreateIssueTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateIssue()
    {
        $issue = $this->getMockBuilder('Qissues\Domain\Model\Request\NewIssue')->disableOriginalConstructor()->getMock();

        $repository = $this->getMock('Qissues\Domain\Model\IssueRepository');
        $repository
            ->expects($this->once())
            ->method('persist')
            ->with($issue)
        ;

        $service = new CreateIssue($repository);
        $service($issue);
    }
}
