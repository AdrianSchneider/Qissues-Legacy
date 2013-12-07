<?php

namespace Qissues\Domain\Service;

use Qissues\Domain\Model\Issue;
use Qissues\Domain\Model\SearchCriteria;
use Qissues\Domain\Service\QueryIssues;
use Qissues\Domain\Shared\Status;

class QueryIssuesTest extends \PHPUnit_Framework_TestCase
{
    public function testQuery()
    {
        $repositoryIssues = array(
            $this->getMockBuilder('Qissues\Domain\Model\Issue')->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder('Qissues\Domain\Model\Issue')->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder('Qissues\Domain\Model\Issue')->disableOriginalConstructor()->getMock()
        );

        $criteria = new SearchCriteria();

        $repository = $this->getMock('Qissues\Domain\Model\IssueRepository');
        $repository
            ->expects($this->once())
            ->method('query')
            ->with($criteria)
            ->will($this->returnValue($repositoryIssues));
        ;

        $service = new QueryIssues($repository);
        $issues = $service($criteria);

        $this->assertInstanceOf('Qissues\Domain\Model\Response\Issues', $issues);
    }

    public function testQuerySortsIfSortFields()
    {
        $repositoryIssues = array(
            new Issue(1, 'a', 'd', new Status('open'), new \DateTime, new \DateTime),
            new Issue(2, 'c', 'd', new Status('open'), new \DateTime, new \DateTime),
            new Issue(3, 'b', 'd', new Status('open'), new \DateTime, new \DateTime)
        );

        $criteria = new SearchCriteria();
        $criteria->addSortField('title');

        $repository = $this->getMock('Qissues\Domain\Model\IssueRepository');
        $repository
            ->expects($this->once())
            ->method('query')
            ->with($criteria)
            ->will($this->returnValue($repositoryIssues));
        ;

        $service = new QueryIssues($repository);
        $issues = $service($criteria);

        $ids = array_map(function($issue) { return $issue->getId(); }, iterator_to_array($issues));
        $this->assertEquals(array(1, 3, 2), $ids);
    }
}
