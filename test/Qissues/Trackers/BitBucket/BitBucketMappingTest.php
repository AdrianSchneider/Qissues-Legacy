<?php

namespace Qissues\Tests\Trackers\BitBucket;

use Qissues\Domain\Shared\User;
use Qissues\Domain\Shared\Status;
use Qissues\Domain\Shared\Priority;
use Qissues\Domain\Shared\Type;
use Qissues\Domain\Shared\Label;
use Qissues\Domain\Model\Issue;
use Qissues\Domain\Model\SearchCriteria;
use Qissues\Domain\Model\Request\NewIssue;
use Qissues\Trackers\BitBucket\BitBucketMapping;

class BitBucketMappingTest extends \PHPUnit_Framework_TestCase
{
    public function testGetExpectedDetails()
    {
        $mapping = new BitBucketMapping();
        $details = $mapping->getExpectedDetails();
        foreach (array('title', 'description', 'assignee', 'type', 'label', 'priority') as $field) {
            $this->assertTrue(isset($details[$field]));
        }
    }

    public function testGetExpectedDetailsForExistingIssue()
    {
        $issue = new Issue(1, 't', 'd', new Status('open'), new \DateTime, new \DateTime, new User('adr'), new Priority(1, 'low'), new Type('bug'), array(new Label('ux')));

        $mapping = new BitBucketMapping();
        $details = $mapping->getExpectedDetails($issue);

        $this->assertEquals('t', $details['title']->getDefault());
        $this->assertEquals('d', $details['description']->getDefault());
        $this->assertEquals('adr', $details['assignee']->getDefault());
        $this->assertEquals('bug', $details['type']->getDefault());
        $this->assertEquals('ux', $details['label']->getDefault());
    }

    public function testToIssueBasics()
    {
        $mapping = new BitBucketMapping();
        $issue = $mapping->toIssue(array(
            'local_id' => 1,
            'title' => 'hello world',
            'content' => 'oh hai',
            'status' => 'new',
            'utc_created_on' => '2013-01-01',
            'utc_last_updated' => '2013-01-01'
        ));

        $this->assertEquals(1, $issue->getId());
        $this->assertEquals('hello world', $issue->getTitle());
        $this->assertEquals('oh hai', $issue->getDescription());
        $this->assertEquals('new', $issue->getStatus()->getStatus());
        $this->assertEquals('2013-01-01', $issue->getDateCreated()->format('Y-m-d'));
        $this->assertEquals('2013-01-01', $issue->getDateUpdated()->format('Y-m-d'));

        $this->assertNull($issue->getAssignee());
        $this->assertNull($issue->getPriority());
        $this->assertNull($issue->getType());
        $this->assertEmpty($issue->getLabels());
    }

    public function testToIssueWithOptionals()
    {
        $mapping = new BitBucketMapping();
        $issue = $mapping->toIssue(array(
            'local_id' => 1,
            'title' => 'hello world',
            'content' => 'oh hai',
            'status' => 'new',
            'utc_created_on' => '2013-01-01',
            'utc_last_updated' => '2013-01-01',
            'responsible' => array('username' => 'adrian', 'display_name' => 'adrsch'),
            'priority' => 'minor',
            'metadata' => array(
                'kind' => 'bug',
                'component' => 'ux'
            )
        ));

        $this->assertEquals('adrian', $issue->getAssignee()->getAccount());
        $this->assertEquals('adrsch', $issue->getAssignee()->getName());
        $this->assertEquals(2, $issue->getPriority()->getPriority());
        $this->assertEquals('bug', $issue->getType()->getName());
        $this->assertEquals('ux', implode(',', array_map('strval', $issue->getLabels())));
    }

    public function testToNewIssueBasics()
    {
        $mapping = new BitBucketMapping();
        $issue = $mapping->toNewIssue(array(
            'title' => 'hello world',
            'description' => 'oh hai'
        ));

        $this->assertEquals('hello world', $issue->getTitle());
        $this->assertEquals('oh hai', $issue->getDescription());
    }

    public function testToNewIssueWithOptionals()
    {
        $mapping = new BitBucketMapping();
        $issue = $mapping->toNewIssue(array(
            'title' => '',
            'description' => '',
            'priority' => 'major',
            'assignee' => 'adrian',
            'type' => 'bug',
            'label' => 'ux'
        ));

        $this->assertEquals('adrian', $issue->getAssignee()->getAccount());
        $this->assertEquals('major', $issue->getPriority()->getName());
        $this->assertEquals('bug', $issue->getType()->getName());
        $this->assertEquals('ux', implode(',', array_map('strval', $issue->getLabels())));
    }

    public function testToNewIssueAlsoAcceptsNumericPriorities()
    {
        $mapping = new BitBucketMapping();
        $issue = $mapping->toNewIssue(array(
            'title' => '',
            'description' => '',
            'priority' => 3
        ));

        $this->assertEquals('major', $issue->getPriority()->getName());
    }

    public function testToNewIssueThrowsExceptionWithMultipleLabels()
    {
        $this->setExpectedException('DomainException', 'single');
        $mapping = new BitBucketMapping();
        $issue = $mapping->toNewIssue(array(
            'title' => '',
            'description' => '',
            'label' => 'bug, feature'
        ));
    }

    public function testIssueToArray()
    {
        $issue = new NewIssue('hello world', 'oh hai', new User('myself'), new Priority(1, 'trivial'), new Type('bug'), array(new Label('ux')));

        $mapping = new BitBucketMapping();
        $data = $mapping->issueToArray($issue);

        $this->assertEquals('hello world', $data['title']);
        $this->assertEquals('oh hai', $data['content']);
        $this->assertEquals('myself', $data['responsible']);
        $this->assertEquals('trivial', $data['priority']);
        $this->assertEquals('bug', $data['kind']);
    }

    public function testToComment()
    {
        $mapping = new BitBucketMapping();
        $comment = $mapping->toComment(array(
            'content' => 'hello world',
            'author_info' => array(
                'username' => 'adr',
                'display_name' => 'adrian'
            ),
            'utc_created_on' => '2013-01-01'
        ));

        $this->assertEquals('hello world', $comment->getMessage());
        $this->assertEquals('adr', $comment->getAuthor()->getAccount());
        $this->assertEquals('adrian', $comment->getAuthor()->getName());
        $this->assertEquals('2013-01-01', $comment->getDate()->format('Y-m-d'));
    }

    public function testQueryFilterByType()
    {
        $criteria = new SearchCriteria();
        $criteria->addType(new Type('bug'));

        $mapping = new BitBucketMapping();
        $query = $mapping->buildSearchQuery($criteria);

        $this->assertEquals(array('bug'), $query['kind']);
    }

    public function testQueryFilterByUnsupportedTypeThrowsException()
    {
        $this->setExpectedException('DomainException', 'type');

        $criteria = new SearchCriteria();
        $criteria->addType(new Type('peanut'));

        $mapping = new BitBucketMapping();
        $mapping->buildSearchQuery($criteria);
    }

    public function testQueryFilterByStatuses()
    {
        $criteria = new SearchCriteria();
        $criteria->addStatus(new Status('resolved'));

        $mapping = new BitBucketMapping();
        $query = $mapping->buildSearchQuery($criteria);

        $this->assertEquals(array('resolved'), $query['status']);
    }

    public function testQueryFilterByUnsupportedStatusThrowsException()
    {
        $this->setExpectedException('DomainException', 'status');

        $criteria = new SearchCriteria();
        $criteria->addStatus(new Status('lame'));

        $mapping = new BitBucketMapping();
        $mapping->buildSearchQuery($criteria);
    }

    public function testQueryFilterByAssignees()
    {
        $criteria = new SearchCriteria();
        $criteria->addAssignee(new User('joe'));

        $mapping = new BitBucketMapping();
        $query = $mapping->buildSearchQuery($criteria);

        $this->assertEquals(array('joe'), $query['responsible']);
    }

    public function testQueryFilterByLabel()
    {
        $criteria = new SearchCriteria();
        $criteria->addLabel(new Label('cool'));

        $mapping = new BitBucketMapping();
        $query = $mapping->buildSearchQuery($criteria);

        $this->assertEquals(array('cool'), $query['component']);
    }

    public function testQueryFilterByKeywords()
    {
        $criteria = new SearchCriteria();
        $criteria->setKeywords('eggnog');

        $mapping = new BitBucketMapping();
        $query = $mapping->buildSearchQuery($criteria);

        $this->assertEquals('eggnog', $query['search']);
    }

    public function testQueryFilterByPriority()
    {
        $criteria = new SearchCriteria();
        $criteria->addPriority(new Priority(0, 'major'));

        $mapping = new BitBucketMapping();
        $query = $mapping->buildSearchQuery($criteria);

        $this->assertEquals(array('major'), $query['priority']);
    }

    public function testQueryFilterByNumericPriority()
    {
        $criteria = new SearchCriteria();
        $criteria->addPriority(new Priority(3, ''));

        $mapping = new BitBucketMapping();
        $query = $mapping->buildSearchQuery($criteria);

        $this->assertEquals(array('major'), $query['priority']);
    }

    public function testQueryFilterByUnsupportedPriorityThrowsException()
    {
        $this->setExpectedException('DomainException', 'priority');

        $criteria = new SearchCriteria();
        $criteria->addPriority(new Priority(99, 'made up'));

        $mapping = new BitBucketMapping();
        $mapping->buildSearchQuery($criteria);
    }

    public function testQueryFilterByNumbersThrowsException()
    {
        $this->setExpectedException('DomainException', 'numbers');

        $criteria = new SearchCriteria();
        $criteria->setNumbers(array(1, 2, 3));

        $mapping = new BitBucketMapping();
        $mapping->buildSearchQuery($criteria);
    }

    public function testQueryPagination()
    {
        $criteria = new SearchCriteria();
        $criteria->setPaging(3, 25);

        $mapping = new BitBucketMapping();
        $query = $mapping->buildSearchQuery($criteria);

        $this->assertEquals(25, $query['limit']);
        $this->assertEquals(50, $query['offset']);
    }

    public function testSorting()
    {
        // TODO
    }

    public function testGetStatusMatching()
    {
        $mapping = new BitBucketMapping();
        $status = $mapping->getStatusMatching(new Status('open'));
        $this->assertEquals('open', $status->getStatus());
    }

    public function testGetStatusFuzzyMatching()
    {
        $mapping = new BitBucketMapping();
        $status = $mapping->getStatusMatching(new Status('hold'));
        $this->assertEquals('on hold', $status->getStatus());
    }

    public function testGetStatusThrowsException()
    {
        $this->setExpectedException('DomainException', 'invalid');
        $mapping = new BitBucketMapping();
        $mapping->getStatusMatching(new Status('pizza'));
    }
}
