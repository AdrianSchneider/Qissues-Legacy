<?php

namespace Qissues\Tests\Trackers\GitHub;

use Qissues\Model\Number;
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
        $criteria = new SearchCriteria();
        $query = array('keyword' => 'meh');
        $payload = array(array('issue'));

        $mapping = $this->getMock('Qissues\Model\Tracker\FieldMapping');
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
}
