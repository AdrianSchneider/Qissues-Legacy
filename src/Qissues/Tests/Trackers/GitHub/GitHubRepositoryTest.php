<?php

namespace Qissues\Tests\Trackers\GitHub;

use Qissues\Model\Number;
use Qissues\Model\Meta\Status;
use Qissues\Model\Meta\Label;
use Qissues\Model\Querying\SearchCriteria;
use Qissues\Trackers\GitHub\GitHubRepository;
use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\Mock\MockPlugin;
use Guzzle\Plugin\History\HistoryPlugin;

class GitHubRepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->mock = new MockPlugin();
        $this->history = new HistoryPlugin();
        $this->client = new Client();
        $this->client->addSubscriber($this->mock);
        $this->client->addSubscriber($this->history);
        $this->config = array(
            'username' => 'adrian',
            'password' => 'yoyoma',
            'repository' => 'adrian/peanuts'
        );
    }

    protected function getRepository($mapping)
    {
        return new GitHubRepository(
            $this->config['username'],
            $this->config['password'],
            $this->config['repository'],
            $mapping,
            $this->client
        );
    }

    public function testLookup()
    {
        $payload = array('issue_datas' => 'yup');

        $mapping = $this->getMock('Qissues\Model\Tracker\FieldMapping');
        $mapping
            ->expects($this->once())
            ->method('toIssue')
            ->with($payload)
            ->will($this->returnValue($out = 'issue'))
        ;

        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $tracker = $this->getRepository($mapping);
        $issue = $tracker->lookup(new Number(5));

        $this->assertEquals($out, $issue);
    }

    public function testQuery()
    {
        $payload = array(array('issue'));

        $mapping = $this->getMock('Qissues\Model\Tracker\FieldMapping');
        $mapping
            ->expects($this->once())
            ->method('toIssue')
            ->with(array('issue'))
            ->will($this->returnValue($out = 'real issue'))
        ;

        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $tracker = $this->getRepository($mapping);
        $issues = $tracker->query(new SearchCriteria());

        $this->assertCount(1, $issues);
        $this->assertEquals('real issue', $issues[0]);
    }

    public function testFilterByLabels()
    {
        $payload = array(array('issue'));

        $mapping = $this->getMock('Qissues\Model\Tracker\FieldMapping');
        $mapping
            ->expects($this->once())
            ->method('toIssue')
            ->with(array('issue'))
            ->will($this->returnValue($out = 'real issue'))
        ;

        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $criteria = new SearchCriteria();
        $criteria->addLabel(new Label('darn'));
        $criteria->addLabel(new Label('it'));

        $tracker = $this->getRepository($mapping);
        $issues = $tracker->query($criteria);

        $this->assertQueryEquals('labels', 'darn,it');
        $this->assertCount(1, $issues);
        $this->assertEquals('real issue', $issues[0]);
    }

    public function testQueryByStatuses()
    {
        $payload = array(array('issue'));

        $mapping = $this->getMock('Qissues\Model\Tracker\FieldMapping');
        $mapping
            ->expects($this->once())
            ->method('toIssue')
            ->with(array('issue'))
            ->will($this->returnValue($out = 'real issue'))
        ;

        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $criteria = new SearchCriteria();
        $criteria->addStatus(new Status('open'));

        $tracker = $this->getRepository($mapping);
        $issues = $tracker->query($criteria);

        $this->assertQueryEquals('state', 'open');
        $this->assertCount(1, $issues);
        $this->assertEquals('real issue', $issues[0]);
    }

    public function testQueryingMultipleStatusesIsUnsupported()
    {
        $criteria = new SearchCriteria();
        $criteria->addStatus(new Status('a'));
        $criteria->addStatus(new Status('b'));

        $mapping = $this->getMock('Qissues\Model\Tracker\FieldMapping');
        $tracker = $this->getRepository($mapping);

        $this->setExpectedException('DomainException');
        $tracker->query($criteria);
    }

    public function testQuerySortingByField()
    {
        $payload = array(array('issue'));

        $mapping = $this->getMock('Qissues\Model\Tracker\FieldMapping');
        $mapping
            ->expects($this->once())
            ->method('toIssue')
            ->with(array('issue'))
            ->will($this->returnValue($out = 'real issue'))
        ;

        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $criteria = new SearchCriteria();
        $criteria->addSortField('comments');

        $tracker = $this->getRepository($mapping);
        $issues = $tracker->query($criteria);

        $this->assertQueryEquals('sort', 'comments');
        $this->assertCount(1, $issues);
        $this->assertEquals('real issue', $issues[0]);
    }

    public function testQueryMultiSortThrowsException()
    {
        $criteria = new SearchCriteria();
        $criteria->addSortField('created');
        $criteria->addSortField('updated');

        $mapping = $this->getMock('Qissues\Model\Tracker\FieldMapping');
        $tracker = $this->getRepository($mapping);

        $this->setExpectedException('DomainException', 'multi-sort');
        $tracker->query($criteria);
    }

    public function testQuerySuportingByUnSupportedFieldThrowsException()
    {
        $criteria = new SearchCriteria();
        $criteria->addSortField('priority');

        $mapping = $this->getMock('Qissues\Model\Tracker\FieldMapping');
        $tracker = $this->getRepository($mapping);

        $this->setExpectedException('DomainException', 'unsupported');
        $tracker->query($criteria);
    }

    public function testQueryingByIdsThrowsException()
    {
        $criteria = new SearchCriteria();
        $criteria->setNumbers(array(1));

        $mapping = $this->getMock('Qissues\Model\Tracker\FieldMapping');
        $tracker = $this->getRepository($mapping);

        $this->setExpectedException('DomainException', 'numbers');
        $tracker->query($criteria);
    }

    public function testQueryPerPage()
    {
        $payload = array(array('issue'));

        $mapping = $this->getMock('Qissues\Model\Tracker\FieldMapping');
        $mapping
            ->expects($this->once())
            ->method('toIssue')
            ->with(array('issue'))
            ->will($this->returnValue($out = 'real issue'))
        ;

        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $criteria = new SearchCriteria();
        $criteria->setPaging(2, 50);

        $tracker = $this->getRepository($mapping);
        $issues = $tracker->query($criteria);

        $this->assertQueryEquals('page', 2);
        $this->assertQueryEquals('per_page', 50);
        $this->assertEquals('real issue', $issues[0]);
    }

    public function testFindComments()
    {
        $payload = array(array('comment'));

        $mapping = $this->getMock('Qissues\Model\Tracker\FieldMapping');
        $mapping
            ->expects($this->once())
            ->method('toComment')
            ->with(array('comment'))
            ->will($this->returnValue($out = 'real comment'))
        ;

        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $tracker = $this->getRepository($mapping);
        $comments = $tracker->findComments(new Number(1));

        $this->assertCount(1, $comments);
        $this->assertEquals('real comment', $comments[0]);
    }

    protected function assertQueryEquals($key, $value)
    {
        $this->assertEquals(
            $value,
            $this->history->getLastRequest()->getQuery()->get($key)
        );
    }
}
