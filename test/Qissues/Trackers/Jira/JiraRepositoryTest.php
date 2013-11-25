<?php

namespace Qissues\Trackers\Jira;

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

    public function testQuery()
    {
        $criteria = new SearchCriteria();
        $query = array('keyword' => 'meh', 'paging' => array('limit' => 5));
        $payload = array('issues' => array(array('issue')));

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
        $issues = $tracker->query(new SearchCriteria());

        $this->assertCount(1, $issues);
        $this->assertEquals('real issue', $issues[0]);
    }

    public function testFindComments()
    {
        $payload = array('comments' => array(array('comment')));

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

    public function testComment()
    {
        $comment = new NewComment('hello world');
        $this->mock->addResponse(new Response(200));

        $repository = $this->getRepository();
        $repository->comment(new Number(5), $comment);

        $this->assertBodyEquals(json_encode(array('body' => 'hello world')));
    }

    public function testPersist()
    {
        $payload = array('key' => 'PRE-12');
        $issue = $this->getMockBuilder('Qissues\Model\Posting\NewIssue')->disableOriginalConstructor()->getMock();
        $serializedIssue = array('issue');
        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $mapping = $this->getMock('Qissues\Model\Tracker\FieldMapping');
        $mapping
            ->expects($this->once())
            ->method('issueToArray')
            ->with($issue)
            ->will($this->returnValue($serializedIssue))
        ;

        $repository = $this->getRepository($mapping);
        $number = $repository->persist($issue);

        $this->assertEquals(12, $number->getNumber());
    }

    public function testAssign()
    {
        $this->mock->addResponse(new Response(200));

        $repository = $this->getRepository();
        $repository->assign(new Number(5), new User('joe'));

        $this->assertRequestMethod('PUT');
        $this->assertRequestUrl("/rest/api/2/issue/PRE-5/assignee");
        $this->assertRequestKeyEquals('name', 'joe');
    }

    public function testJiraDoesNotSupportStatusChanges()
    {
        $this->setExpectedException('DomainException', 'later');

        $repository = $this->getRepository();
        $repository->changeStatus(new Number(1), new Status('never'));
    }

    public function testDelete()
    {
        $this->mock->addResponse(new Response(200));

        $repository = $this->getRepository();
        $repository->delete(new Number(5));

        $this->assertRequestMethod('DELETE');
        $this->assertRequestUrl("/rest/api/2/issue/PRE-5");
    }

    public function testFetchMetadataThrowsExceptionWhenCannotFind()
    {
        $this->mock->addResponse(new Response(200, null, json_encode(array('projects' => array()))));
        $this->setExpectedException('Exception', 'not find');

        $repository = $this->getRepository();
        $repository->fetchMetadata();
    }

    public function testFetchMetadataGrabsRightProject()
    {
        $this->mock->addResponse(new Response(200, null, json_encode(array(
            'projects' => array(
                array( 'id' => 1, 'key' => 'wrongprefix', 'issuetypes' => array()),
                array( 'id' => 2, 'key' => 'PRE', 'issuetypes' => array())
            )
        ))));
        $this->mock->addResponse(new Response(200, null, json_encode(array('issueTypes' => array()))));
        $this->mock->addResponse(new Response(200, null, json_encode(array())));

        $repository = $this->getRepository();
        $metadata = $repository->fetchMetadata();

        $this->assertEquals(2, $metadata['id']);
    }

    public function testFetchMetadataGrabsTypes()
    {
        $this->mock->addResponse(new Response(200, null, json_encode(array(
            'projects' => array(
                array( 'id' => 1, 'key' => 'wrongprefix', 'issuetypes' => array()),
                array( 'id' => 2, 'key' => 'PRE', 'issuetypes' => array())
            )
        ))));
        $this->mock->addResponse(new Response(200, null, json_encode(array(
            'issueTypes' => array(
                array(
                    'id' => 1,
                    'name' => 'bug'
                )
            )
        ))));
        $this->mock->addResponse(new Response(200, null, json_encode(array(
            array(
                'id' => 1,
                'statuses' => array(
                    array(
                        'id' => 10,
                        'name' => 'new'
                    ),
                    array(
                        'id' => 12,
                        'name' => 'fixed'
                    )
                )
            )
        ))));

        $repository = $this->getRepository();
        $metadata = $repository->fetchMetadata();

        $this->assertEquals(array(
            'id' => 1,
            'name' => 'bug',
            'statuses' => array(
                array( 'id' => 10, 'name' => 'new'),
                array( 'id' => 12, 'name' => 'fixed')
            )
        ), $metadata['types'][1]);
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

    protected function assertBodyEquals($body, $toString = false)
    {
        $lastBody = $this->history->getLastRequest()->getBody();
        if ($toString) {
            $lastBody = strval($lastBody);
        }

        $this->assertEquals($body, $lastBody);
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
