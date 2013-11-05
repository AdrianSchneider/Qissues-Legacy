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
    public function testToIssueCreatesIssue()
    {
        $mapping = new JiraMapping('test');
        $issue = $mapping->toIssue(array(
            'key' => 'PREFIX-5',
            'fields' => array(
                'summary' => 'New Issue',
                'description' => 'The Details',
                'status' => array(
                    'name' => 'fixed'
                ),
                'assignee' => array(
                    'name' => 'Adrian'
                ),
                'created' => '01/02/2013',
                'updated' => '01/01/2013',
                'priority' => array(
                    'id' => 5,
                    'name' => 'urgent'
                ),
                'issuetype' => array(
                    'type' => 'bug'
                )
            )
        ));

        $this->assertEquals(5, $issue->getId());
        $this->assertEquals('New Issue', $issue->getTitle());
        $this->assertEquals('The Details', $issue->getDescription());
        $this->assertEquals('fixed', $issue->getStatus()->getStatus());
        $this->assertEquals('urgent', $issue->getPriority()->getName());
    }

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

    public function testQueryFilterByTypes()
    {
        $criteria = new SearchCriteria();
        $criteria->addType(new Type('resolved'));
        $criteria->addType(new Type('fixed'));

        $mapping = new JiraMapping('proj');
        $query = $mapping->buildSearchQuery($criteria);

        $this->assertEquals(array('resolved', 'fixed'), $query['issuetype']);
    }

    public function testQuerySortByPrioritySortsDesc()
    {
        $criteria = new SearchCriteria();
        $criteria->addSortField('priority');

        $mapping = new JiraMapping('proj');
        $query = $mapping->buildSearchQuery($criteria);

        $this->assertEquals(array('priority DESC'), $query['sort']);
    }

    public function testQueryByUnsupportedFieldThrowsException()
    {
        $this->setExpectedException('Exception', 'unsupported sort field');

        $criteria = new SearchCriteria();
        $criteria->addSortField('whatsdat');

        $mapping = new JiraMapping('proj');
        $mapping->buildSearchQuery($criteria);
    }

    public function testQueryPagination()
    {
        $criteria = new SearchCriteria();
        $criteria->setPaging(5, 10);

        $mapping = new JiraMapping('proj');
        $query = $mapping->buildSearchQuery($criteria);

        $this->assertEquals(array('startAt' => 40, 'maxResults' => 10), $query['paging']);
    }

    public function testToCommentCreatesComment()
    {
        $mapping = new JiraMapping('test');
        $comment = $mapping->toComment(array(
            'body' => 'message',
            'author' => array('name' => 'adrian'),
            'created' => '2013-01-01'
        ));

        $this->assertEquals('message', $comment->getMessage());
        $this->assertEquals('adrian', $comment->getAuthor());
        $this->assertEquals('2013-01-01', $comment->getDate()->format('2013-01-01'));
    }
}
