<?php

namespace Qissues\Trackers\Jira;

use Qissues\Trackers\Jira\JiraMapping;
use Qissues\Trackers\Jira\JiraMetadata;
use Qissues\Model\Meta\User;
use Qissues\Model\Meta\Status;
use Qissues\Model\Meta\Priority;
use Qissues\Model\Meta\Type;
use Qissues\Model\Meta\Label;
use Qissues\Model\Querying\SearchCriteria;

class JiraMappingTest extends \PHPUnit_Framework_TestCase
{
    public function testGetEditFields()
    {
        $mapping = $this->getMapping(array());

        $this->assertEquals(
            array('title', 'assignee', 'type', 'label', 'priority', 'description'),
            array_keys($mapping->getEditFields())
        );
    }

    public function testToIssueCreatesIssue()
    {
        $mapping = $this->getMapping(array());
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

    public function testBuildSearchQuery()
    {
        $criteria = new SearchCriteria();
        $criteria->setPaging(5, 10);

        $jql = $this->getMockBuilder('Qissues\Trackers\Jira\JqlQueryBuilder')->disableOriginalConstructor()->getMock();
        $jql
            ->expects($this->once())
            ->method('build')
            ->with($criteria)
            ->will($this->returnValue('SELECT * FROM jira'))
        ;

        $mapping = $this->getMapping(array(), $jql);
        $query = $mapping->buildSearchQuery($criteria);

        $this->assertEquals('SELECT * FROM jira', $query['jql']);
        $this->assertEquals(40, $query['startAt']);
        $this->assertEquals(10, $query['maxResults']);
    }

    public function testToCommentCreatesComment()
    {
        $mapping = $this->getMapping();
        $comment = $mapping->toComment(array(
            'body' => 'message',
            'author' => array('name' => 'adrian'),
            'created' => '2013-01-01'
        ));

        $this->assertEquals('message', $comment->getMessage());
        $this->assertEquals('adrian', $comment->getAuthor());
        $this->assertEquals('2013-01-01', $comment->getDate()->format('2013-01-01'));
    }

    protected function getMapping($metadata = array(), $jql = null)
    {
        return new JiraMapping(
            new JiraMetadata($metadata),
            $jql ?: $this->getMockBuilder('Qissues\Trackers\Jira\JqlQueryBuilder')->disableOriginalConstructor()->getMock()
        );
    }
}
