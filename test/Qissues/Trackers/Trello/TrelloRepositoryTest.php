<?php

namespace Qissues\Trackers\Trello;

use Qissues\Domain\Model\Number;
use Qissues\Domain\Shared\User;
use Qissues\Domain\Shared\Status;
use Qissues\Domain\Shared\ClosedStatus;
use Qissues\Domain\Model\Message;
use Qissues\Trackers\Trello\TrelloRepository;
use Qissues\Trackers\Trello\TrelloMetadata;
use Qissues\Application\Tracker\Metadata\NullMetadata;
use Qissues\Domain\Model\SearchCriteria;
use Guzzle\Http\Client;
use Guzzle\Http\Message\Response;
use Guzzle\Plugin\Mock\MockPlugin;
use Guzzle\Plugin\History\HistoryPlugin;

class TrelloRepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->mock = new MockPlugin();
        $this->history = new HistoryPlugin();
        $this->client = new Client();
        $this->client->addSubscriber($this->mock);
        $this->client->addSubscriber($this->history);
        $this->config = array(
            'board' => 'testing',
            'key' => 'devkey',
            'token' => 'usertoken'
        );
    }

    protected function getRepository($mapping = null, $metadata = null)
    {
        return new TrelloRepository(
            $this->config['board'],
            $this->config['key'],
            $this->config['token'],
            $mapping ?: $this->getMockBuilder('Qissues\Application\Tracker\FieldMapping')->disableOriginalConstructor()->getMock(),
            $metadata ? new TrelloMetadata($metadata) : new NullMetadata(),
            $this->client
        );
    }

    public function testGetUrl()
    {
        $tracker = $this->getRepository(null, array('id' => 12));
        $this->assertEquals('https://trello.com/b/12', $tracker->getUrl());
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

        $metadata = array('id' => 5);

        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $tracker = $this->getRepository($mapping, $metadata);
        $issue = $tracker->lookup(new Number(5));

        $this->assertEquals($out, $issue);
    }

    public function testLookupUrl()
    {
        $payload = array('url' => 'http://google.com');
        $metadata = array('id' => 5);

        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $tracker = $this->getRepository(null, $metadata);
        $url = $tracker->lookupUrl(new Number(5));

        $this->assertEquals('http://google.com', $url);
    }

    public function testQuery()
    {
        $criteria = new SearchCriteria();
        $query = array('params' => array('a' => 'b'), 'endpoint' => '/here');
        $payload = array(array('issue'));
        $issues = array('an issue yo');

        $mapping = $this->getMockBuilder('Qissues\Trackers\Trello\TrelloMapping')->disableOriginalConstructor()->getMock();
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
            ->will($this->returnValue($issues[0]))
        ;

        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $tracker = $this->getRepository($mapping);
        $queried = $tracker->query($criteria);

        $this->assertCount(1, $issues);
        $this->assertEquals($queried, $issues);

        $this->assertEquals("/1/here?key=devkey&token=usertoken&a=b", $this->history->getLastRequest()->getUrl());
    }

    public function testQueryKeywordsReturnsCardsPortion()
    {
        $criteria = new SearchCriteria();
        $query = array('params' => array('a' => 'b'), 'endpoint' => '/here');
        $payload = array('cards' => array(array('issue')));
        $issues = array('an issue yo');

        $mapping = $this->getMockBuilder('Qissues\Trackers\Trello\TrelloMapping')->disableOriginalConstructor()->getMock();
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
            ->will($this->returnValue($issues[0]))
        ;

        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $tracker = $this->getRepository($mapping);
        $queried = $tracker->query($criteria);

        $this->assertCount(1, $issues);
        $this->assertEquals($queried, $issues);

        $this->assertEquals("/1/here?key=devkey&token=usertoken&a=b", $this->history->getLastRequest()->getUrl());
    }

    public function testFindComments()
    {
        $issue = new Number(1337);
        $payload = array('actions' => array($rawComment = array('comment')));
        $comment = array('a real comment');

        $mapping = $this->getMockBuilder('Qissues\Trackers\Trello\TrelloMapping')->disableOriginalConstructor()->getMock();
        $mapping
            ->expects($this->once())
            ->method('toComment')
            ->with($rawComment)
            ->will($this->returnValue($comment))
        ;

        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $tracker = $this->getRepository($mapping, array('id' => 5));
        $comments = $tracker->findComments($issue);

        $this->assertCount(1, $comments);
        $path = $this->history->getLastRequest()->getPath();

        $this->assertEquals('/1/boards/5/cards/1337', $this->history->getLastRequest()->getPath());
        $this->assertEquals('commentCard', $this->history->getLastRequest()->getQuery()->get('actions'));
    }

    public function testPersist()
    {
        $issue = $this->getMockBuilder('Qissues\Domain\Model\Request\NewIssue')->disableOriginalConstructor()->getMock();
        $payload = array('idShort' => 500);

        $mapping = $this->getMockBuilder('Qissues\Trackers\Trello\TrelloMapping')->disableOriginalConstructor()->getMock();
        $mapping
            ->expects($this->once())
            ->method('issueToArray')
            ->with($issue)
            ->will($this->returnValue(array('title' => 'blah')))
        ;

        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $tracker = $this->getRepository($mapping);
        $number = $tracker->persist($issue);

        $this->assertEquals(500, $number->getNumber());

        $this->assertMethod('POST');
        $this->assertPath('/cards');
        $this->assertRequestBodyEquals('title', 'blah');
    }

    public function testUpdate()
    {
        $payload = array();
        $number = new Number(12);
        $issue = $this->getMockBuilder('Qissues\Domain\Model\Request\NewIssue')->disableOriginalConstructor()->getMock();

        $mapping = $this->getMockBuilder('Qissues\Trackers\Trello\TrelloMapping')->disableOriginalConstructor()->getMock();
        $mapping
            ->expects($this->once())
            ->method('issueToArray')
            ->with($issue)
            ->will($this->returnValue(array('title' => 'blah')))
        ;

        $this->mock->addResponse(new Response(200, null, json_encode(array('id' => 'asdf'))));
        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $tracker = $this->getRepository($mapping, array('id' => 5));
        $tracker->update($issue, $number);

        $this->assertMethod('PUT');
        $this->assertPath("/cards/asdf");
        $this->assertRequestBodyEquals('title', 'blah');
    }

    public function testComment()
    {
        $payload = array();
        $number = new Number(12);
        $comment = new Message('sup');

        $this->mock->addResponse(new Response(200, null, json_encode(array('id' => 'asdf'))));
        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $tracker = $this->getRepository(null, array('id' => 5));
        $tracker->comment($number, $comment);

        $this->assertMethod('POST');
        $this->assertPath("/cards/asdf/actions/comments");
    }

    public function testDelete()
    {
        $payload = array();
        $number = new Number(12);

        $this->mock->addResponse(new Response(200, null, json_encode(array('id' => 'asdf'))));
        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $tracker = $this->getRepository(null, array('id' => 5));
        $tracker->delete($number);

        $this->assertMethod('DELETE');
        $this->assertPath("/cards/asdf");
    }

    public function testChangeStatus()
    {
        $payload = array();
        $number = new Number(12);

        $this->mock->addResponse(new Response(200, null, json_encode(array('id' => 'asdf'))));
        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $tracker = $this->getRepository(null, array('id' => 5, 'lists' => array(array('id' => 3, 'name' => 'open'))));
        $tracker->changeStatus($number, new Status('open'));

        $this->assertMethod('PUT');
        $this->assertPath("/cards/asdf");
        $this->assertRequestBodyEquals('idList', 3);
    }

    public function testChangeStatusArchivesForClosed()
    {
        $payload = array();
        $number = new Number(12);

        $this->mock->addResponse(new Response(200, null, json_encode(array('id' => 'asdf'))));
        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $tracker = $this->getRepository(null, array('id' => 5));
        $tracker->changeStatus($number, new ClosedStatus());

        $this->assertMethod('PUT');
        $this->assertPath("/cards/asdf");
        $this->assertRequestBodyEquals('closed', true);
    }

    public function testAssign()
    {
        $payload = array();
        $number = new Number(1);

        $this->mock->addResponse(new Response(200, null, json_encode(array('id' => '1337'))));
        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $tracker = $this->getRepository(null, array('id' => 5, 'members' => array(array('id' => 5, 'username' => 'adrian', 'fullName' => 'adrsch'))));
        $tracker->assign($number, new User('adrian'));

        $this->assertMethod('PUT');
        $this->assertPath("/cards/1337");
        $this->assertRequestBodyEquals('idMembers', 5);
    }

    public function testFetchMetadata()
    {
        $this->mock->addResponse(new Response(200, null, json_encode(array(
            'id' => 'asdf',
            'username' => 'adrian'
        ))));

        $payload = array(
            array(
                'id' => 2,
                'name' => 'testing',
                'lists' => array(
                    array(
                        'id' => 1,
                        'name' => 'List',
                        'pos' => 5
                    )
                ),
                'labelNames' => array(
                    'red' => 'THE RED ONE'
                )
            )
        );

        $this->mock->addResponse(new Response(200, null, json_encode($payload)));
        $this->mock->addResponse(new Response(200, null, json_encode(array(
            'members' => array(
                array(
                    'id' => 88,
                    'username' => 'joe',
                    'fullName' => 'Joseph'
                )
            )
        ))));

        $repository = $this->getRepository();
        $info = $repository->fetchMetadata();

        $this->assertEquals(array(
            'id' => 2,
            'me' => array(
                'id' => 'asdf',
                'username' => 'adrian'
            ),
            'name' => 'testing',
            'lists' => array(
                array(
                    'id' => 1,
                    'name' => 'List',
                    'pos' => 5
                )
            ),
            'labels' => array(
                'red' => 'THE RED ONE'
            ),
            'members' => array(
                array(
                    'id' => 88,
                    'username' => 'joe',
                    'fullName' => 'Joseph'
                )
            )

        ), $info);
    }

    public function testFetchMetadataSortsListsByPos()
    {
        $this->mock->addResponse(new Response(200, null, json_encode(array(
            'id' => 1,
            'username' => 'asdf'
        ))));

        $payload = array(
            array(
                'id' => 2,
                'name' => 'testing',
                'lists' => array( 
                    array( 'id' => 1, 'name' => 'A', 'pos' => 3),
                    array( 'id' => 2, 'name' => 'B', 'pos' => 2),
                    array( 'id' => 3, 'name' => 'C', 'pos' => 1)
                ),
                'labelNames' => array()
            )
        );

        $this->mock->addResponse(new Response(200, null, json_encode($payload)));
        $this->mock->addResponse(new Response(200, null, json_encode(array('members' => array()))));

        $repository = $this->getRepository();
        $info = $repository->fetchMetadata();

        $this->assertEquals('C', $info['lists'][0]['name']);
        $this->assertEquals('B', $info['lists'][1]['name']);
        $this->assertEquals('A', $info['lists'][2]['name']);
    }

    public function testFetchMetadataThrowsExceptionIfBoardNotFound()
    {
        $this->mock->addResponse(new Response(200, null, json_encode(array(
            'id' => 1, 
            'username' => ''
        ))));

        $payload = array(
            array(
                'id' => 2,
                'name' => 'something we dont want',
                'lists' => array(
                    array(
                        'id' => 1,
                        'name' => 'List',
                        'pos' => 5
                    )
                ),
                'labelNames' => array(
                    'red' => 'THE RED ONE'
                )
            )
        );

        $this->mock->addResponse(new Response(200, null, json_encode($payload)));

        $this->setExpectedException('Exception', 'board');

        $repository = $this->getRepository();
        $repository->fetchMetadata();
    }

    protected function assertMethod($method)
    {
        $this->assertEquals($method, $this->history->getLastRequest()->getMethod());
    }

    protected function assertRequestBodyEquals($key, $value)
    {
        $body = json_decode($this->history->getLastRequest()->getBody(), true);
        $this->assertEquals($value, $body[$key]);
    }

    protected function assertPath($path)
    {
        $this->assertEquals(
            "/1$path",
            $this->history->getLastRequest()->getPath(),
            "Wrong path"
        );
    }
}
