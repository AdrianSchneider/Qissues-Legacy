<?php

namespace Qissues\Domain\Service;

use Qissues\Domain\Service\QueryIssues;
use Qissues\Domain\Model\SearchCriteria;

class QueryIssuesTest extends \PHPUnit_Framework_TestCase
{
    public function testQuery()
    {
        $out = array(1, 2, 3);
        $criteria = new SearchCriteria();

        $repository = $this->getMock('Qissues\Domain\Model\IssueRepository');
        $repository
            ->expects($this->once())
            ->method('query')
            ->with($criteria)
            ->will($this->returnValue($out));
        ;

        $service = new QueryIssues($repository);
        $issues = $service($criteria);

        $this->assertEquals($out, $issues);
    }
}
