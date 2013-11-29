<?php

namespace Qissues\Tests\Trackers\GitHub;

use Qissues\Domain\Model\Number;
use Qissues\Domain\Shared\User;
use Qissues\Domain\Shared\Status;
use Qissues\Domain\Model\NewComment;
use Qissues\Domain\Model\SearchCriteria;
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

    protected function getRepository($mapping = null)
    {
        return new GitHubRepository(
            $this->config['repository'],
            $this->config['username'],
            $this->config['password'],
            $mapping ?: $this->getMockBuilder('Qissues\Application\Tracker\FieldMapping')->disableOriginalConstructor()->getMock(),
            $this->client
        );
    }

    public function testGetUrl()
    {
        $repository = $this->getRepository();
        $this->assertEquals("https://github.com/adrian/peanuts/issues", $repository->getUrl());
    }

    public function testLookup()
    {
        $payload = array('issue_datas' => 'yup');

        $mapping = $this->getMock('Qissues\Application\Tracker\FieldMapping');
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

    public function testLookupUrl()
    {
        $repository = $this->getRepository();
        $url = $repository->lookupUrl(new Number(5));
        $this->assertEquals("https://github.com/adrian/peanuts/issues/5", $url);
    }

    public function testQuery()
    {
        $criteria = new SearchCriteria();
        $query = array('keyword' => 'meh');
        $payload = array(array('issue'));

        $mapping = $this->getMock('Qissues\Application\Tracker\FieldMapping');
        $mapping
            ->expects($this->once())
            ->method('buildSearchQuery')
            ->with($criteria)
            ->will($this->returnValue($query))
        ;
        $mapping
            ->expects($this->once())
            ->method('toIssue')
            ->with(array('issue'))
            ->will($this->returnValue($out = 'real issue'))
        ;

        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $tracker = $this->getRepository($mapping);
        $issues = $tracker->query($criteria);

        $this->assertCount(1, $issues);
        $this->assertEquals('real issue', $issues[0]);
    }

    public function testFindComments()
    {
        $payload = array(array('comment'));

        $mapping = $this->getMock('Qissues\Application\Tracker\FieldMapping');
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

    public function testPersist()
    {
        $issue = $this->getMockBuilder('Qissues\Domain\Model\Newissue')->disableOriginalConstructor()->getMock();
        $payload = array('number' => 5);
        $mapped = array('a' => 'b');

        $mapping = $this->getMock('Qissues\Application\Tracker\FieldMapping');
        $mapping
            ->expects($this->once())
            ->method('issueToArray')
            ->with($issue)
            ->will($this->returnValue($mapped))
        ;

        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $tracker = $this->getRepository($mapping);
        $number = $tracker->persist($issue);

        $this->assertEquals(5, $number->getNumber());
    }

    public function testUpdate()
    {
        $number = new Number(5);
        $issue = $this->getMockBuilder('Qissues\Domain\Model\Newissue')->disableOriginalConstructor()->getMock();
        $mapped = array('a' => 'b');
        $payload = array();

        $mapping = $this->getMock('Qissues\Application\Tracker\FieldMapping');
        $mapping
            ->expects($this->once())
            ->method('issueToArray')
            ->with($issue)
            ->will($this->returnValue($mapped))
        ;

        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $tracker = $this->getRepository($mapping);
        $number = $tracker->update($issue, $number);

        $this->assertRequestMethod('PATCH');
        $this->assertRequestUrl("/repos/adrian/peanuts/issues/5");
        $this->assertRequestKeyEquals('a', 'b');
    }

    public function testComment()
    {
        $payload = array();
        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $tracker = $this->getRepository();
        $tracker->comment(new Number(5), new NewComment('sup'));

        $this->assertRequestMethod('POST');
        $this->assertRequestUrl("/repos/adrian/peanuts/issues/5/comments");
        $this->assertRequestKeyEquals('body', 'sup');
    }

    public function testAssign()
    {
        $payload = array();
        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $tracker = $this->getRepository();
        $tracker->assign(new Number(5), new User('joe'));

        $this->assertRequestMethod('PATCH');
        $this->assertRequestUrl("/repos/adrian/peanuts/issues/5");
        $this->assertRequestKeyEquals('assignee', 'joe');
    }

    public function testChangeStatus()
    {
        $payload = array();
        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $tracker = $this->getRepository();
        $tracker->changeStatus(new Number(5), new Status('open'));

        $this->assertRequestMethod('PATCH');
        $this->assertRequestUrl("/repos/adrian/peanuts/issues/5");
        $this->assertRequestKeyEquals('state', 'open');
    }

    public function testDeleteThrowsException()
    {
        $this->setExpectedException('DomainException', 'cannot');
        $repository = $this->getRepository();
        $repository->delete(new Number(1));
    }

    public function testNoMetadataNeeded()
    {
        $this->setExpectedException('DomainException');
        $repository = $this->getRepository();
        $repository->fetchMetadata();
    }

    protected function assertRequestMethod($method)
    {
        $this->assertEquals(
            $method,
            $this->history->getLastRequest()->getMethod()
        );
    }

    protected function assertRequestUrl($url)
    {
        $this->assertEquals(
            $url,
            $this->history->getLastRequest()->getPath()
        );
    }

    protected function assertRequestKeyEquals($key, $value)
    {
        $body = json_decode((string)$this->history->getLastRequest()->getBody(), true);
        $this->assertEquals($value, $body[$key]);
    }
}
