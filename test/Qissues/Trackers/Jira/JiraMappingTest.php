<?php

namespace Qissues\Trackers\Jira;

use Qissues\Trackers\Jira\JiraMapping;
use Qissues\Trackers\Jira\JiraMetadata;
use Qissues\Model\Posting\NewIssue;
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

    public function testToNewIssueCreatesBasicIssue()
    {
        $input = array(
            'title' => 'Hello World',
            'description' => 'Nice to meet you'
        );

        $mapping = $this->getMapping();
        $issue = $mapping->tonewIssue($input);

        $this->assertEquals('Hello World', $issue->getTitle());
        $this->assertEquals('Nice to meet you', $issue->getDescription());
    }

    public function testToNewIssueWithOptionalFields()
    {
        $input = array(
            'title' => '',
            'description' => '',
            'assignee' => 'adrian',
            'priority' => 'important',
            'type' => 'bug',
            'label' => 'wowza'
        );

        $mapping = $this->getMapping();
        $issue = $mapping->tonewIssue($input);

        $this->assertEquals('adrian', $issue->getAssignee()->getAccount());
        $this->assertEquals('important', $issue->getPriority()->getName());
        $this->assertEquals('bug', $issue->getType()->getName());
        $this->assertEquals('wowza', end($issue->getLabels())->getName());
    }

    public function testToNewIssueThrowsArrayWithMultipleLabels()
    {
        $input = array(
            'title' => '',
            'description' => '',
            'assignee' => '',
            'priority' => '',
            'type' => '',
            'label' => 'a, b, c'
        );

        $this->setExpectedException('DomainException', 'single label');

        $mapping = $this->getMapping();
        $mapping->tonewIssue($input);
    }

    public function testIssueToArray()
    {
        $mapping = $this->getMapping(array(
            'id' => 5,
            'types' => array(
                array('id' => 1, 'name' => 'bug')
            )
        ));

        $issue = new NewIssue("Hello World", "Nice to meet you", null, null, new Type('bug'));
        $array = $mapping->issueToArray($issue);

        $this->assertEquals(array(
            'fields' => array(
                'project' => array('id' => 5),
                'summary' => "Hello World",
                'description' => "Nice to meet you",
                'issuetype' => array('id' => 1)
            )
        ), $array);
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
