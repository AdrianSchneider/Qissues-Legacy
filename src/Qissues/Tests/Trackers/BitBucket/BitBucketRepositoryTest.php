<?php

namespace Qissues\Tests\Trackers\BitBucket;

use Qissues\Model\Number;
use Qissues\Model\Meta\Status;
use Qissues\Model\Meta\ClosedStatus;
use Qissues\Model\Meta\Label;
use Qissues\Model\Meta\Type;
use Qissues\Model\Meta\User;
use Qissues\Model\Meta\Priority;
use Qissues\Model\Posting\NewComment;
use Qissues\Model\Querying\SearchCriteria;
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

    public function testLookupUrl()
    {
        $repository = $this->getRepository();
        $url = $repository->lookupUrl(new Number(5));

        $this->assertEquals('https://bitbucket.org/repo/sitory/issue/5', $url);

    }

    public function testQuery()
    {
        $payload = array('issues' => array(array('issue')));

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

    public function testQueryFilterByType()
    {
        $payload = array('issues' => array(array('issue')));

        $mapping = $this->getMock('Qissues\Model\Tracker\FieldMapping');
        $mapping
            ->expects($this->once())
            ->method('toIssue')
            ->with(array('issue'))
            ->will($this->returnValue($out = 'real issue'))
        ;

        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $criteria = new SearchCriteria();
        $criteria->addType(new Type('bug'));

        $tracker = $this->getRepository($mapping);
        $issues = $tracker->query($criteria);

        $this->assertCount(1, $issues);
        $this->assertEquals('real issue', $issues[0]);
        $this->assertQueryEquals('kind', array('bug'));
    }

    public function testQueryFilterByUnsupportedTypeThrowsException()
    {
        $this->setExpectedException('DomainException', 'type');

        $criteria = new SearchCriteria();
        $criteria->addType(new Type('peanut'));

        $tracker = $this->getRepository();
        $tracker->query($criteria);
    }

    public function testQueryFilterByStatuses()
    {
        $payload = array('issues' => array(array('issue')));

        $mapping = $this->getMock('Qissues\Model\Tracker\FieldMapping');
        $mapping
            ->expects($this->once())
            ->method('toIssue')
            ->with(array('issue'))
            ->will($this->returnValue($out = 'real issue'))
        ;

        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $criteria = new SearchCriteria();
        $criteria->addStatus(new Status('resolved'));

        $tracker = $this->getRepository($mapping);
        $issues = $tracker->query($criteria);

        $this->assertCount(1, $issues);
        $this->assertEquals('real issue', $issues[0]);
        $this->assertQueryEquals('status', array('resolved'));
    }

    public function testQueryFilterByUnsupportedStatusThrowsException()
    {
        $this->setExpectedException('DomainException', 'status');

        $criteria = new SearchCriteria();
        $criteria->addStatus(new Status('lame'));

        $tracker = $this->getRepository();
        $tracker->query($criteria);
    }

    public function testQueryFilterByAssignees()
    {
        $payload = array('issues' => array(array('issue')));

        $mapping = $this->getMock('Qissues\Model\Tracker\FieldMapping');
        $mapping
            ->expects($this->once())
            ->method('toIssue')
            ->with(array('issue'))
            ->will($this->returnValue($out = 'real issue'))
        ;

        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $criteria = new SearchCriteria();
        $criteria->addAssignee(new User('joe'));

        $tracker = $this->getRepository($mapping);
        $issues = $tracker->query($criteria);

        $this->assertCount(1, $issues);
        $this->assertEquals('real issue', $issues[0]);
        $this->assertQueryEquals('responsible', array('joe'));
    }

    public function testQueryFilterByLabel()
    {
        $payload = array('issues' => array(array('issue')));

        $mapping = $this->getMock('Qissues\Model\Tracker\FieldMapping');
        $mapping
            ->expects($this->once())
            ->method('toIssue')
            ->with(array('issue'))
            ->will($this->returnValue($out = 'real issue'))
        ;

        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $criteria = new SearchCriteria();
        $criteria->addLabel(new Label('cool'));

        $tracker = $this->getRepository($mapping);
        $issues = $tracker->query($criteria);

        $this->assertCount(1, $issues);
        $this->assertEquals('real issue', $issues[0]);
        $this->assertQueryEquals('component', array('cool'));
    }

    public function testQueryFilterByKeywords()
    {
        $payload = array('issues' => array(array('issue')));

        $mapping = $this->getMock('Qissues\Model\Tracker\FieldMapping');
        $mapping
            ->expects($this->once())
            ->method('toIssue')
            ->with(array('issue'))
            ->will($this->returnValue($out = 'real issue'))
        ;

        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $criteria = new SearchCriteria();
        $criteria->setKeywords('eggnog');

        $tracker = $this->getRepository($mapping);
        $issues = $tracker->query($criteria);

        $this->assertCount(1, $issues);
        $this->assertEquals('real issue', $issues[0]);
        $this->assertQueryEquals('search', 'eggnog');
    }

    public function testQueryFilterByPriority()
    {
        $payload = array('issues' => array(array('issue')));

        $mapping = $this->getMock('Qissues\Model\Tracker\FieldMapping');
        $mapping
            ->expects($this->once())
            ->method('toIssue')
            ->with(array('issue'))
            ->will($this->returnValue($out = 'real issue'))
        ;

        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $criteria = new SearchCriteria();
        $criteria->addPriority(new Priority(3, 'major'));

        $tracker = $this->getRepository($mapping);
        $issues = $tracker->query($criteria);

        $this->assertCount(1, $issues);
        $this->assertEquals('real issue', $issues[0]);
        $this->assertQueryEquals('priority', array('major'));
    }

    public function testQueryFilterByUnsupportedPriorityThrowsException()
    {
        $this->setExpectedException('DomainException', 'priority');

        $criteria = new SearchCriteria();
        $criteria->addPriority(new Priority(99, 'made up'));

        $tracker = $this->getRepository();
        $tracker->query($criteria);
    }

    public function testQueryFilterByNumbersThrowsException()
    {
        $this->setExpectedException('DomainException', 'numbers');

        $criteria = new SearchCriteria();
        $criteria->setNumbers(array(1, 2, 3));

        $tracker = $this->getRepository();
        $tracker->query($criteria);
    }

    public function testSorting()
    {
        // TODO
    }

    public function testQueryPagination()
    {
        $payload = array('issues' => array(array('issue')));

        $mapping = $this->getMock('Qissues\Model\Tracker\FieldMapping');
        $mapping
            ->expects($this->once())
            ->method('toIssue')
            ->with(array('issue'))
            ->will($this->returnValue($out = 'real issue'))
        ;

        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $criteria = new SearchCriteria();
        $criteria->setPaging(3, 25);

        $tracker = $this->getRepository($mapping);
        $issues = $tracker->query($criteria);

        $this->assertCount(1, $issues);
        $this->assertEquals('real issue', $issues[0]);
        $this->assertQueryEquals('limit', 25);
        $this->assertQueryEquals('offset', 50);
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

    public function testPersist()
    {
        $newIssue = $this->getMockBuilder('Qissues\Model\Posting\NewIssue')->disableOriginalConstructor()->getMock();
        $issue = 'real issue';

        $this->mock->addResponse(new Response(200, null, json_encode($payload = array(
            'local_id' => 5
        ))));

        $mapping = $this->getMock('Qissues\Model\Tracker\FieldMapping');
        $mapping
            ->expects($this->once())
            ->method('issueToArray')
            ->with($newIssue)
            ->will($this->returnValue($issue))
        ;

        $repository = $this->getRepository($mapping);
        $number = $repository->persist($newIssue);

        $this->assertEquals(new Number(5), $number);
        $this->assertBodyEquals($issue);
    }

    public function testUpdate()
    {
        $number = new Number(4);
        $issue = 'saved';
        $newIssue = $this->getMockBuilder('Qissues\Model\Posting\NewIssue')->disableOriginalConstructor()->getMock();

        $this->mock->addResponse(new Response(200));

        $mapping = $this->getMock('Qissues\Model\Tracker\FieldMapping');
        $mapping
            ->expects($this->once())
            ->method('issueToArray')
            ->with($newIssue)
            ->will($this->returnValue($issue))
        ;

        $repository = $this->getRepository($mapping);
        $repository->update($newIssue, $number);

        $this->assertBodyEquals($issue);
    }

    public function testComment()
    {
        $comment = new NewComment('hello world');
        $this->mock->addResponse(new Response(200));

        $repository = $this->getRepository();
        $number = $repository->comment(new Number(5), $comment);

        $this->assertBodyEquals('content=hello+world');
    }

    public function testDelete()
    {
        $this->mock->addResponse(new Response(204));

        $repository = $this->getRepository();
        $repository->delete(new Number(5));
    }

    public function testChangeStatus()
    {
        $this->mock->addResponse(new Response(200));

        $repository = $this->getRepository();
        $repository->changeStatus(new Number(5), new Status('resolved'));

        $this->assertBodyEquals('status=resolved');
    }

    public function testChangeStatusConvertsClosedToResolved()
    {
        $this->mock->addResponse(new Response(200));

        $repository = $this->getRepository();
        $repository->changeStatus(new Number(5), new ClosedStatus());

        $this->assertBodyEquals('status=resolved');
    }

    public function testAssign()
    {
        $this->mock->addResponse(new Response(200));

        $repository = $this->getRepository();
        $number = $repository->assign(new Number(5), new User('adrian'));

        $this->assertBodyEquals('responsible=adrian');
    }

    protected function getRepository($mapping = null)
    {
        $mapping = $mapping ?: $this->getMock('Qissues\Model\Tracker\FieldMapping');

        return new BitBucketRepository(
            'repo/sitory',
            'username',
            'password',
            $mapping,
            $this->client
        );
    }

    protected function assertQueryEquals($key, $value)
    {
        $this->assertEquals(
            $value,
            $this->history->getLastRequest()->getQuery()->get($key)
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
}
