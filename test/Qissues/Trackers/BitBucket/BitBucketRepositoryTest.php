<?php

namespace Qissues\Tests\Trackers\BitBucket;

use Qissues\Domain\Model\Number;
use Qissues\Domain\Shared\Status;
use Qissues\Domain\Shared\ClosedStatus;
use Qissues\Domain\Shared\Label;
use Qissues\Domain\Shared\Type;
use Qissues\Domain\Shared\User;
use Qissues\Domain\Shared\Priority;
use Qissues\Domain\Model\Message;
use Qissues\Domain\Model\SearchCriteria;
use Qissues\Trackers\BitBucket\BitBucketRepository;
use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\Mock\MockPlugin;
use Guzzle\Plugin\History\HistoryPlugin;

class BitBucketRepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->mock = new MockPlugin();
        $this->history = new HistoryPlugin();
        $this->client = new Client();
        $this->client->addSubscriber($this->mock);
        $this->client->addSubscriber($this->history);
    }

    public function testGetUrlInjectsRepository()
    {
        $repository = $this->getRepository();
        $this->assertEquals('https://bitbucket.org/repo/sitory/issues', $repository->getUrl());
    }

    public function testLookupReturnsIssue()
    {
        $payload = array('issue' => 'yup');

        $mapping = $this->getMock('Qissues\Application\Tracker\FieldMapping');
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

    public function testLookupUrl()
    {
        $repository = $this->getRepository();
        $url = $repository->lookupUrl(new Number(5));

        $this->assertEquals('https://bitbucket.org/repo/sitory/issue/5', $url);

    }

    public function testQuery()
    {
        $criteria = new SearchCriteria();
        $query = array('keyword' => 'meh');
        $payload = array('issues' => array(array('issue')));

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
        $issues = $tracker->query(new SearchCriteria());

        $this->assertCount(1, $issues);
        $this->assertEquals('real issue', $issues[0]);

        $this->assertRequestMethod('GET');
        $this->assertRequestUrl('/1.0/repositories/repo/sitory/issues');
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
        $this->assertRequestMethod('GET');
        $this->assertRequestUrl('/1.0/repositories/repo/sitory/issues/1/comments');
    }

    public function testPersist()
    {
        $newIssue = $this->getMockBuilder('Qissues\Domain\Model\Request\NewIssue')->disableOriginalConstructor()->getMock();
        $issue = 'real issue';

        $this->mock->addResponse(new Response(200, null, json_encode($payload = array(
            'local_id' => 5
        ))));

        $mapping = $this->getMock('Qissues\Application\Tracker\FieldMapping');
        $mapping
            ->expects($this->once())
            ->method('issueToArray')
            ->with($newIssue)
            ->will($this->returnValue($issue))
        ;

        $repository = $this->getRepository($mapping);
        $number = $repository->persist($newIssue);

        $this->assertEquals(new Number(5), $number);
        $this->assertRequestMethod('POST');
        $this->assertRequestUrl('/1.0/repositories/repo/sitory/issues');
        $this->assertBodyEquals($issue);
    }

    public function testUpdate()
    {
        $number = new Number(4);
        $issue = 'saved';
        $newIssue = $this->getMockBuilder('Qissues\Domain\Model\Request\NewIssue')->disableOriginalConstructor()->getMock();

        $this->mock->addResponse(new Response(200));

        $mapping = $this->getMock('Qissues\Application\Tracker\FieldMapping');
        $mapping
            ->expects($this->once())
            ->method('issueToArray')
            ->with($newIssue)
            ->will($this->returnValue($issue))
        ;

        $repository = $this->getRepository($mapping);
        $repository->update($newIssue, $number);

        $this->assertBodyEquals($issue);
        $this->assertRequestMethod('PUT');
        $this->assertRequestUrl('/1.0/repositories/repo/sitory/issues/4');
    }

    public function testComment()
    {
        $comment = new Message('hello world');
        $this->mock->addResponse(new Response(200));

        $repository = $this->getRepository();
        $number = $repository->comment(new Number(5), $comment);

        $this->assertRequestMethod('POST');
        $this->assertRequestUrl('/1.0/repositories/repo/sitory/issues/5/comments');
        $this->assertBodyEquals('content=hello+world');
    }

    public function testDelete()
    {
        $this->mock->addResponse(new Response(204));

        $repository = $this->getRepository();
        $repository->delete(new Number(5));

        $this->assertRequestMethod('DELETE');
        $this->assertRequestUrl('/1.0/repositories/repo/sitory/issues/5');
    }

    public function testChangeStatus()
    {
        $status = new Status('resolved');
        $this->mock->addResponse(new Response(200));

        $mapping = $this->getMockBuilder('Qissues\Trackers\BitBucket\BitBucketMapping')->disableOriginalConstructor()->getMock();
        $mapping
            ->expects($this->once())
            ->method('getStatusMatching')
            ->with($status)
            ->will($this->returnValue($status))
        ;

        $repository = $this->getRepository($mapping);
        $repository->changeStatus(new Number(5), $status);

        $this->assertBodyEquals('status=resolved');
    }

    public function testChangeStatusConvertsClosedToResolved()
    {
        $status = new ClosedStatus();
        $this->mock->addResponse(new Response(200));

        $mapping = $this->getMockBuilder('Qissues\Trackers\BitBucket\BitBucketMapping')->disableOriginalConstructor()->getMock();
        $mapping
            ->expects($this->once())
            ->method('getStatusMatching')
            ->will($this->returnCallback(function($status) {
                return $status;
            }))
        ;

        $repository = $this->getRepository($mapping);
        $repository->changeStatus(new Number(5), $status);

        $this->assertBodyEquals('status=resolved');
    }

    public function testAssign()
    {
        $this->mock->addResponse(new Response(200));

        $repository = $this->getRepository();
        $number = $repository->assign(new Number(5), new User('adrian'));

        $this->assertBodyEquals('responsible=adrian');
    }

    public function testFetchMetadataThrowsDomainException()
    {
        $this->setExpectedException('DomainException', 'No metadata');
        $this->getRepository()->fetchMetadata();
    }

    protected function getRepository($mapping = null)
    {
        $mapping = $mapping ?: $this->getMock('Qissues\Application\Tracker\FieldMapping');

        return new BitBucketRepository(
            'repo/sitory',
            'username',
            'password',
            $mapping,
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
