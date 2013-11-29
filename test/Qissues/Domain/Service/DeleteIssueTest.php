<?php

namespace Qissues\Domain\Service;

use Qissues\Domain\Model\Number;
use Qissues\Domain\Service\DeleteIssue;

class DeleteIssueTest extends \PHPUnit_Framework_TestCase
{
    public function testDelete()
    {
        $issue = new Number(5);

        $repository = $this->getMock('Qissues\Domain\Model\IssueRepository');
        $repository
            ->expects($this->once())
            ->method('delete')
            ->with($issue)
        ;

        $service = new DeleteIssue($repository);
        $service($issue);
    }
}
