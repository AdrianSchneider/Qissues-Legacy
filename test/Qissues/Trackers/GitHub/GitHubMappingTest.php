<?php

namespace Qissues\Tests\Trackers\GitHub;

use Qissues\Domain\Model\NewIssue;
use Qissues\Trackers\GitHub\GitHubMapping;
use Qissues\Domain\Meta\User;
use Qissues\Domain\Meta\Status;
use Qissues\Domain\Meta\Type;
use Qissues\Domain\Meta\Label;
use Qissues\Domain\Meta\Priority;
use Qissues\Domain\Model\SearchCriteria;

class GitHubMappingTest extends \PHPUnit_Framework_TestCase
{
    public function testToIssueCreatesAnIssue()
    {
        $converter = new GitHubMapping();
        $issue = $converter->toIssue(array(
            'number' => 1,
            'title' => 'Hello World',
            'body' => 'Oh snap',
            'created_at' => 'now',
            'updated_at' => 'now',
            'state' => 'open',
            'assignee' => '',
            'labels' => ''
        ));

        $this->assertInstanceOf('Qissues\Domain\Model\Issue', $issue);
        $this->assertEquals(1, $issue->getId());
        $this->assertEquals('Hello World', $issue->getTitle());
        $this->assertEquals('Oh snap', $issue->getDescription());
    }

    public function testToNewIssueCreatesANewIssue()
    {
        $mapping = new GitHubMapping();
        $issue = $mapping->toNewIssue(array(
            'title' => 'Hello World',
            'description' => 'Oh snap'
        ));

        $this->assertInstanceOf('Qissues\Domain\Model\NewIssue', $issue);
        $this->assertEquals('Hello World', $issue->getTitle());
        $this->assertEquals('Oh snap', $issue->getDescription());
    }

    public function testIssueToArrayConverts()
    {
        $converter = new GitHubMapping();
        $issue = new NewIssue('Hello', 'World');
        $rawIssue = $converter->issueToArray($issue);

        $this->assertEquals('Hello', $rawIssue['title']);
        $this->assertEquals('World', $rawIssue['body']);
    }

    public function testFilterByLabels()
    {
        $criteria = new SearchCriteria();
        $criteria->addLabel(new Label('darn'));
        $criteria->addLabel(new Label('it'));

        $mapping = new GitHubMapping();
        $query = $mapping->buildSearchQuery($criteria);

        $this->assertEquals('darn,it', $query['labels']);
    }

    public function testQueryByStatuses()
    {
        $criteria = new SearchCriteria();
        $criteria->addStatus(new Status('open'));

        $mapping = new GitHubMapping();
        $query = $mapping->buildSearchQuery($criteria);

        $this->assertEquals('open', $query['state']);
    }

    public function testQueryingMultipleStatusesIsUnsupported()
    {
        $this->setExpectedException('DomainException', 'multiple statuses');

        $criteria = new SearchCriteria();
        $criteria->addStatus(new Status('a'));
        $criteria->addStatus(new Status('b'));

        $mapping = new GitHubMapping();
        $mapping->buildSearchQuery($criteria);
    }

    public function testQuerySortingByField()
    {
        $criteria = new SearchCriteria();
        $criteria->addSortField('comments');

        $mapping = new GitHubMapping();
        $query = $mapping->buildSearchQuery($criteria);

        $this->assertEquals('comments', $query['sort']);
    }

    public function testQueryMultiSortThrowsException()
    {
        $this->setExpectedException('DomainException', 'multi-sort');

        $criteria = new SearchCriteria();
        $criteria->addSortField('created');
        $criteria->addSortField('updated');

        $mapping = new GitHubMapping();
        $mapping->buildSearchQuery($criteria);
    }

    public function testQuerySuportingByUnSupportedFieldThrowsException()
    {
        $this->setExpectedException('DomainException', 'unsupported');

        $criteria = new SearchCriteria();
        $criteria->addSortField('priority');

        $mapping = new GitHubMapping();
        $mapping->buildSearchQuery($criteria);
    }

    public function testQueryingByIdsThrowsException()
    {
        $this->setExpectedException('DomainException', 'numbers');

        $criteria = new SearchCriteria();
        $criteria->setNumbers(array(1));

        $mapping = new GitHubMapping();
        $mapping->buildSearchQuery($criteria);
    }

    public function testQueryingByKeywordsThrowsException()
    {
        $this->setExpectedException('DomainException', 'keywords');

        $criteria = new SearchCriteria();
        $criteria->setKeywords('sadface');

        $mapping = new GitHubMapping();
        $mapping->buildSearchQuery($criteria);
    }

    public function testQueryingByPriorityThrowsException()
    {
        $this->setExpectedException('DomainException', 'priority');

        $criteria = new SearchCriteria();
        $criteria->addPriority(new Priority(1, 'meh'));

        $mapping = new GitHubMapping();
        $mapping->buildSearchQuery($criteria);
    }

    public function testQueryPerPage()
    {
        $criteria = new SearchCriteria();
        $criteria->setPaging(2, 50);

        $mapping = new GitHubMapping();
        $query = $mapping->buildSearchQuery($criteria);

        $this->assertEquals(2, $query['page']);
        $this->assertEquals(50, $query['per_page']);
    }
}
