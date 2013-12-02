<?php

namespace Qissues\Trackers\Trello;

use Qissues\Domain\Model\Issue;
use Qissues\Domain\Shared\Status;
use Qissues\Domain\Shared\Label;
use Qissues\Domain\Shared\Priority;
use Qissues\Domain\Model\SearchCriteria;
use Qissues\Trackers\Trello\TrelloMapping;

class TrelloMappingTest extends \PHPUnit_Framework_TestCase
{
    public function testGetExpectedDetails()
    {
        $mapping = $this->getMapper(array('lists' => array(array('id' => 1, 'name' => 'First'))));
        $details = $mapping->getExpectedDetails();

        $this->assertInstanceOf('Qissues\Domain\Shared\ExpectedDetails', $details);

        foreach (array('title', 'description', 'status', 'labels', 'assignee', 'priority') as $field) {
            $this->assertTrue(isset($details[$field]));
        }
    }

    public function testGetExpectedDetailsListDefaultsToFirst()
    {
        $mapping = $this->getMapper(array('lists' => array(
            array('id' => 1, 'name' => 'First'),
            array('id' => 2, 'name' => 'Middle'),
            array('id' => 3, 'name' => 'Last')
        )));

        $details = $mapping->getExpectedDetails();

        $this->assertEquals('First', $details['status']->getDefault());
    }

    public function testToIssueBasic()
    {
        $date = new \DateTime();
        $trelloCard = array(
            'idShort' => 1,
            'name' => 'Pizza',
            'desc' => 'ham and pineapples',
            'idList' => 8,
            'dateLastActivity' => $date->format('Y-m-d g:ia'),
            'labels' => array()
        );

        $mapper = $this->getMapper(array('lists' => array(array( 'id' => 8, 'name' => 'New'))));
        $issue = $mapper->toIssue($trelloCard);

        $this->assertEquals($trelloCard['idShort'], $issue->getId());
        $this->assertEquals($trelloCard['name'], $issue->getTitle());
        $this->assertEquals($trelloCard['desc'], $issue->getDescription());
        $this->assertEquals('New', $issue->getStatus()->getStatus());
        $this->assertEquals($date->format('u'), $issue->getDateCreated()->format('u'));
        $this->assertEquals($date->format('u'), $issue->getDateUpdated()->format('u'));
        $this->assertEmpty($issue->getLabels());
    }

    public function testToIssueThrowsExceptionIfCannotMapStatus()
    {
        $date = new \DateTime();
        $trelloCard = array(
            'idShort' => 1,
            'name' => 'Pizza',
            'desc' => 'ham and pineapples',
            'idList' => 200,
            'dateLastActivity' => $date->format('Y-m-d g:ia'),
            'labels' => array()
        );

        $this->setExpectedException('LogicException', 'update');

        $mapper = $this->getMapper(array('lists' => array()));
        $issue = $mapper->toIssue($trelloCard);
    }

    public function testToIssueConvertsChecklistsIntoDescription()
    {
        $date = new \DateTime();
        $trelloCard = array(
            'idShort' => 1,
            'name' => 'Pizza',
            'desc' => '',
            'idList' => 8,
            'dateLastActivity' => $date->format('Y-m-d g:ia'),
            'labels' => array(),
            'checklists' => array(
                array(
                    'name' => 'Checklist',
                    'checkItems' => array(
                        array(
                            'name' => "Don't forget the milk",
                            'state' => 'complete'
                        )
                    )
                )
            )
        );

        $mapper = $this->getMapper(array('lists' => array(array( 'id' => 8, 'name' => 'New'))));
        $issue = $mapper->toIssue($trelloCard);

        $this->assertEquals("Checklists:\n\nChecklist\n    [x] Don't forget the milk", $issue->getDescription());
    }

    public function testToNewIssue()
    {
        $mapper = $this->getMapper(array('lists' => array(
            array('id' => 1, 'name' => 'New') 
        )));

        $issue = $mapper->toNewIssue(array(
            'title' => 'Hello World',
            'description' => 'Why, hello there!',
            'status' => 'New'
        ));

        $this->assertEquals('Hello World', $issue->getTitle());
        $this->assertEquals('Why, hello there!', $issue->getDescription());
        $this->assertEquals('New', $issue->getStatus()->getStatus());
        $this->assertNull($issue->getPriority());
    }

    public function testToNewIssueStatusAllowsTopOrBottom()
    {
        $mapper = $this->getMapper(array('lists' => array(
            array('id' => 1, 'name' => 'New') 
        )));

        $issue = $mapper->toNewIssue(array(
            'title' => '',
            'description' => '',
            'status' => 'New',
            'priority' => 'top',
        ));

        $this->assertEquals('top', $issue->getPriority()->getName());
    }

    public function testQueryByKeywords()
    {
        $criteria = new SearchCriteria();
        $criteria->setKeywords('hello world');

        $mapper = $this->getMapper(array('id' => 5));
        $query = $mapper->buildSearchQuery($criteria);

        $this->assertEquals("/search", $query['endpoint']);
        $this->assertEquals("hello world", $query['params']['query']);
        $this->assertEquals(5, $query['params']['idBoards']);
    }

    public function testQueryBySingleStatus()
    {
        $criteria = new SearchCriteria();
        $criteria->addStatus(new Status('New'));

        $mapper = $this->getMapper(array('lists' => array(array( 'name' => 'New', 'id' => 5))));
        $query = $mapper->buildSearchQuery($criteria);

        $this->assertEquals("/lists/5/cards", $query['endpoint']);
    }

    public function testQueryByPriorityThrowsException()
    {
        $criteria = new SearchCriteria();
        $criteria->addPriority(new Priority(5, 'meh'));

        $this->setExpectedException('DomainException', 'priority');

        $mapper = $this->getMapper(array('lists' => array(array( 'name' => 'New', 'id' => 5))));
        $mapper->buildSearchQuery($criteria);
    }

    public function testFilterIssuesByStatuses()
    {
        $criteria = new SearchCriteria();
        $criteria->addStatus(new Status('New'));

        $issues = array(
            new MockIssue(new Status('New')),
            new MockIssue(new Status('Open'))
        );

        $mapper = $this->getMapper(array());
        $filtered = $mapper->filterIssues($issues, $criteria);

        $this->assertCount(1, $filtered);
        $this->assertSame($issues[0], $filtered[0]);
    }

    public function testFilterIssuesByLazyStatuses()
    {
        $criteria = new SearchCriteria();
        $criteria->addStatus(new Status('open'));

        $issues = array(
            new MockIssue(new Status('Open for Business')),
            new MockIssue(new Status('Closed'))
        );

        $mapper = $this->getMapper(array());
        $filtered = $mapper->filterIssues($issues, $criteria);

        $this->assertCount(1, $filtered);
        $this->assertSame($issues[0], $filtered[0]);
    }

    public function testFilterIssuesByLabels()
    {
        $criteria = new SearchCriteria();
        $criteria->addLabel(new Label('blue'));

        $issues = array(
            new MockIssue(null, array(new Label('blue'))),
            new MockIssue(null, array(new Label('red')))
        );

        $mapper = $this->getMapper(array());
        $filtered = $mapper->filterIssues($issues, $criteria);

        $this->assertCount(1, $filtered);
        $this->assertSame($issues[0], $filtered[0]);
    }

    public function testFilterSortByCreated()
    {
        $criteria = new SearchCriteria();
        $criteria->addSortField('created');

        $issues = array(
            new SortableMock(new \DateTime, 1),
            new SortableMock(new \DateTime, 5)
        );

        $mapper = $this->getMapper(array());
        $filtered = $mapper->filterIssues($issues, $criteria);

        $this->assertSame($issues[0], $filtered[1]);
        $this->assertSame($issues[1], $filtered[0]);
    }

    public function testFilterSortByUpdated()
    {
        $criteria = new SearchCriteria();
        $criteria->addSortField('updated');

        $issues = array(
            new SortableMock(new \DateTime('-10 seconds'), 1),
            new SortableMock(new \DateTime('+10 seconds'), 1)
        );

        $mapper = $this->getMapper(array());
        $filtered = $mapper->filterIssues($issues, $criteria);

        $this->assertSame($issues[0], $filtered[1]);
        $this->assertSame($issues[1], $filtered[0]);
    }

    public function testFilterThrowsExceptionOnInvalidSortField()
    {
        $criteria = new SearchCriteria();
        $criteria->addSortField('bananas');

        $mapper = $this->getMapper(array());

        $this->setExpectedException('DomainException', 'unsupported');
        $mapper->filterIssues(array(), $criteria);
    }

    protected function getMapper(array $board)
    {
        return new TrelloMapping(new TrelloMetadata($board));
    }
}

class MockIssue extends Issue
{
    protected $status;
    protected $labels;

    public function __construct(Status $status = null, array $labels = array())
    {
        $this->status = $status;
        $this->labels = $labels;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getLabels()
    {
        return $this->labels;
    }
}

class SortableMock extends Issue
{
    public function __construct(\DateTime $updated, $id)
    {
        $this->dateUpdated = $updated;
        $this->id = $id;
    }
}
