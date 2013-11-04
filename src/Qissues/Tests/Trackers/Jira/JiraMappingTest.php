<?php

namespace Qissues\Tests\Trackers\Jira;

use Qissues\Trackers\Jira\JiraMapping;
use Qissues\Model\Meta\User;
use Qissues\Model\Meta\Status;
use Qissues\Model\Meta\Priority;
use Qissues\Model\Meta\Type;
use Qissues\Model\Meta\Label;
use Qissues\Model\Querying\SearchCriteria;

class JiraMappingTest extends \PHPUnit_Framework_TestCase
{
    public function testQueryFiltersByProjectAutomatically()
    {
        $mapping = new JiraMapping('test');
        $query = $mapping->buildSearchQuery(new SearchCriteria());

        $this->assertEquals('test', $query['project']);
    }

    public function testQueryFilterByAssignees()
    {
        $criteria = new SearchCriteria();
        $criteria->addAssignee(new User('adrian'));

        $mapping = new JiraMapping('project');
        $query = $mapping->buildSearchQuery($criteria);

        $this->assertEquals(array('adrian'), $query['assignee']);
    }

    public function testQueryFilterByStatuses()
    {
        $criteria = new SearchCriteria();
        $criteria->addStatus(new Status('resolved'));
        $criteria->addStatus(new Status('fixed'));

        $mapping = new JiraMapping('proj');
        $query = $mapping->buildSearchQuery($criteria);

        $this->assertEquals(array('resolved', 'fixed'), $query['status']);
    }

    public function testQueryFilterByUnsupportedStatusThrowsException()
    {
        // TODO
    }
}
