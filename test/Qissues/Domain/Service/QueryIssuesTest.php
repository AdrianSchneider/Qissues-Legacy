<?php

namespace Qissues\Domain\Service;

use Qissues\Domain\Service\QueryIssues;
use Qissues\Domain\Model\SearchCriteria;

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
}
