<?php

namespace Qissues\Trackers\Jira;

use Qissues\Trackers\Jira\JiraMapping;
use Qissues\Trackers\Jira\JiraMetadata;
use Qissues\Domain\Model\Request\NewIssue;
use Qissues\Domain\Shared\User;
use Qissues\Domain\Shared\Status;
use Qissues\Domain\Shared\Priority;
use Qissues\Domain\Shared\Type;
use Qissues\Domain\Shared\Label;
use Qissues\Domain\Model\SearchCriteria;

class JiraMappingTest extends \PHPUnit_Framework_TestCase
{
    public function testGetsCorrectExpectedDetails()
    {
        $mapping = $this->getMapping(array( 'components' => array()));
        $details = $mapping->getExpectedDetails();

        $this->assertInstanceOf('Qissues\Domain\Shared\ExpectedDetails', $details);

        foreach (array('title', 'description', 'assignee', 'type', 'priority', 'labels') as $field) {
            $this->assertTrue(isset($details[$field]));
        }
    }

    public function testExpectsCorrectDefaults()
    {
        $mapping = $this->getMapping(array(
            'types' => array(
                array('id' => 1, 'name' => 'New Feature')
            ),
            'components' => array()
        ));

        $details = $mapping->getExpectedDetails();

        $this->assertEquals('', $details['title']->getDefault());
        $this->assertEquals('', $details['description']->getDefault());
        $this->assertEquals('', $details['assignee']->getDefault());
        $this->assertEquals('', $details['type']->getDefault());
        $this->assertEquals(3, $details['priority']->getDefault());
    }

    public function testExpectsCorrectOptions()
    {
        $mapping = $this->getMapping(array(
            'components' => array(
                array('id' => 1, 'name' => 'a'),
                array('id' => 2, 'name' => 'b'),
                array('id' => 3, 'name' => 'c'),
            )
        ));

        $details = $mapping->getExpectedDetails();

        $this->assertEquals(range(1, 5), $details['priority']->getOptions());
        $this->assertEquals(array('a', 'b', 'c'), $details['labels']->getOptions());
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
                'issuetype' => array(
                    'id' => 12,
                    'name' => 'bug'
                ),
                'priority' => array(
                    'id' => 5,
                    'name' => 'urgent'
                ),
                'issuetype' => array(
                    'name' => 'bug'
                ),
                'components' => array(array(
                    'id' => 4,
                    'name' => 'Important'
                )),
                'comment' => array('total' => 5)
            )
        ));
        $labels = $issue->getLabels();

        $this->assertEquals(5, $issue->getId());
        $this->assertEquals('New Issue', $issue->getTitle());
        $this->assertEquals('The Details', $issue->getDescription());
        $this->assertEquals('fixed', $issue->getStatus()->getStatus());
        $this->assertEquals('bug', $issue->getType()->getName());
        $this->assertEquals('urgent', $issue->getPriority()->getName());
        $this->assertEquals('Important', $labels[0]->getName());
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
            'labels' => ''
        );

        $mapping = $this->getMapping();
        $issue = $mapping->tonewIssue($input);
        $labels = $issue->getLabels();

        $this->assertEquals('adrian', $issue->getAssignee()->getAccount());
        $this->assertEquals('important', $issue->getPriority()->getName());
        $this->assertEquals('bug', $issue->getType()->getName());
    }

    public function testToNewIssueLabelsFuzzyMatch()
    {
        $input = array(
            'title' => '',
            'description' => '',
            'assignee' => 'adrian',
            'priority' => 'important',
            'type' => 'bug',
            'labels' => 'symfony jira'
        );

        $mapping = $this->getMapping(array(
            'id' => 5,
            'components' => array(
                array('id' => 5, 'name' => 'symfony console'),
                array('id' => 7, 'name' => 'i love|hate jira')
            )
        ));

        $issue = $mapping->toNewIssue($input);
        $labels = $issue->getLabels();

        $this->assertEquals('symfony console', $labels[0]->getName());
        $this->assertEquals('i love|hate jira', $labels[1]->getName());
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

    public function testIssueToArrayWithAssignee()
    {
        $mapping = $this->getMapping(array(
            'id' => 5,
            'types' => array(
                array('id' => 1, 'name' => 'bug')
            )
        ));

        $issue = new NewIssue("Hello World", "Nice to meet you", new User('me'), null, new Type('bug'));
        $array = $mapping->issueToArray($issue);

        $this->assertEquals('me', $array['fields']['assignee']['name']);
    }

    public function testIssueToArrayWithLabels()
    {
        $mapping = $this->getMapping(array(
            'id' => 5,
            'types' => array(
                array('id' => 1, 'name' => 'bug')
            )
        ));

        $labels = array(
            new Label('a'),
            new Label('b')
        );

        $issue = new NewIssue("Hello World", "Nice to meet you", new User('me'), null, new Type('bug'), $labels);
        $array = $mapping->issueToArray($issue);

        $this->assertEquals('a', $array['fields']['components'][0]['name']);
        $this->assertEquals('b', $array['fields']['components'][1]['name']);
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
