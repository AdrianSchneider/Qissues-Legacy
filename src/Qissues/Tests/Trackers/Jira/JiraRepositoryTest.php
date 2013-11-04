<?php

namespace Qissues\Tests\Trackers\Jira;

use Qissues\Model\Number;
use Qissues\Model\Meta\Status;
use Qissues\Model\Meta\ClosedStatus;
use Qissues\Model\Meta\Label;
use Qissues\Model\Meta\Type;
use Qissues\Model\Meta\User;
use Qissues\Model\Meta\Priority;
use Qissues\Model\Posting\NewComment;
use Qissues\Model\Querying\SearchCriteria;
use Qissues\Trackers\Jira\JiraRepository;
use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\Mock\MockPlugin;
use Guzzle\Plugin\History\HistoryPlugin;

class JiraRepositoryTest extends \PHPUnit_Framework_TestCase
{ 
    public function setUp()
    {
        $this->mock = new MockPlugin();
        $this->history = new HistoryPlugin();
        $this->client = new Client();
        $this->client->addSubscriber($this->mock);
        $this->client->addSubscriber($this->history);
    }

    public function testGetUrl()
    {
        $repository = $this->getRepository();
        $url = $repository->getUrl();
        $this->assertEquals('https://project.atlassian.net/issues', $url);
    }

    public function testLookupUrlReturnsUrl()
    {
        $repository = $this->getRepository();
        $url = $repository->lookupUrl(new Number(5));
        $this->assertEquals('https://project.atlassian.net/browse/PRE-5', $url);
    }

    public function testLookupReturnsIssue()
    {
        $payload = array('issue' => 'yup');

        $mapping = $this->getMock('Qissues\Model\Tracker\FieldMapping');
        $mapping
            ->expects($this->once())
            ->method('toIssue')
            ->with($payload)
            ->will($this->returnValue($out = 'Issue'))
        ;

        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $repository = $this->getRepository($mapping);
        $issue = $repository->lookup(new Number(5));

        $this->assertEquals($out, $issue);
    }

    protected function getRepository($mapping = null)
    {
        return new JiraRepository(
            'project',
            'PRE',
            'username',
            'password',
            $mapping ?: $this->getMock('Qissues\Model\Tracker\FieldMapping'),
            $this->client
        );
    }
}
