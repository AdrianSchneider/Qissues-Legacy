<?php

namespace Qissues\Tests\Trackers\GitHub;

use Qissues\Model\Number;
use Qissues\Model\Status;
use Qissues\Model\SearchCriteria;
use Qissues\Trackers\GitHub\GitHubTracker;
use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\Mock\MockPlugin;

class GitHubTrackerTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->mock = new MockPlugin();
        $this->client = new Client();
        $this->client->addSubscriber($this->mock);
        $this->config = array(
            'username' => 'adrian',
            'password' => 'yoyoma',
            'repository' => 'adrian/peanuts'
        );
    }

    public function testLookup()
    {
        $payload = array('issue_datas' => 'yup');

        $converter = $this->getMockBuilder('Qissues\Trackers\GitHub\GitHubConverter')->disableOriginalConstructor()->getMock();
        $converter
            ->expects($this->once())
            ->method('toIssue')
            ->with($payload)
            ->will($this->returnValue($out = 'issue'))
        ;

        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $tracker = new GitHubTracker($this->config, $this->client, $converter);
        $issue = $tracker->lookup(new Number(5));

        $this->assertEquals('issue', $issue);
    }

    public function testQuery()
    {
        $payload = array(array('issue'));

        $converter = $this->getMockBuilder('Qissues\Trackers\GitHub\GitHubConverter')->disableOriginalConstructor()->getMock();
        $converter
            ->expects($this->once())
            ->method('toIssue')
            ->with(array('issue'))
            ->will($this->returnValue($out = 'real issue'))
        ;

        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $tracker = new GitHubTracker($this->config, $this->client, $converter);
        $issues = $tracker->query(new SearchCriteria());

        $this->assertCount(1, $issues);
        $this->assertEquals('real issue', $issues[0]);
    }

    public function testQueryingMultipleStatusesIsUnsupported()
    {
        $criteria = new SearchCriteria();
        $criteria->addStatus(new Status('a'));
        $criteria->addStatus(new Status('b'));

        $this->setExpectedException('DomainException');

        $tracker = new GitHubTracker($this->config, $this->client);
        $issues = $tracker->query($criteria);
    }

    public function testFindComments()
    {
        $payload = array(array('comment'));

        $converter = $this->getMockBuilder('Qissues\Trackers\GitHub\GitHubConverter')->disableOriginalConstructor()->getMock();
        $converter
            ->expects($this->once())
            ->method('toComment')
            ->with(array('comment'))
            ->will($this->returnValue($out = 'real comment'))
        ;

        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $tracker = new GitHubTracker($this->config, $this->client, $converter);
        $comments = $tracker->findComments(new Number(1));

        $this->assertCount(1, $comments);
        $this->assertEquals('real comment', $comments[0]);
    }
}
