<?php

namespace Qissues\Trackers\InMemory;

use Qissues\Domain\Shared\User;
use Qissues\Domain\Shared\Status;
use Qissues\Domain\Model\Number;
use Qissues\Domain\Model\Request\NewIssue;
use Qissues\Domain\Model\Request\NewComment;
use Qissues\Trackers\InMemory\InMemoryRepository;

class InMemoryRepositoryTest extends \PHPUnit_Framework_TestCase
{
    public function testGetUrlThrowsException()
    {
        $this->setExpectedException('Exception', 'testing');
        $repo = new InMemoryRepository();
        $repo->getUrl();
    }

    public function testLookupUrlThrowsException()
    {
        $this->setExpectedException('Exception', 'testing');
        $repo = new InMemoryRepository();
        $repo->lookupUrl(new Number(5));
    }

    public function testCreates()
    {
        $repo = new InMemoryRepository();
        $repo->persist(new NewIssue('a', 'b'));
        
        $issues = $repo->getIssues();
        $this->assertEquals('a', $issues[1]['title']);
        $this->assertEquals('b', $issues[1]['description']);
    }

    public function testUpdates()
    {
        $repo = new InMemoryRepository(array(
            1 => array(
                'number' => 1,
                'title' => 'a',
                'description' => 'b'
            )
        ));

        $repo->update(new NewIssue('c', 'd'), new Number(1));

        $issues = $repo->getIssues();
        $this->assertEquals('c', $issues[1]['title']);
        $this->assertEquals('d', $issues[1]['description']);
    }

    public function testDeletes()
    {
        $repo = new InMemoryRepository(array(
            1 => array(
                'title' => 'a',
                'description' => 'b',
                'status' => 'new'
            )
        ));

        $repo->delete(new Number(1));
        $this->assertEmpty($repo->getIssues());
    }

    public function testLookup()
    {
        $repo = new InMemoryRepository(array(
            1 => array(
                'number' => 1,
                'title' => 'a',
                'description' => 'b',
                'status' => 'new'
            )
        ));

        $issue = $repo->lookup(new Number(1));

        $this->assertInstanceOf('Qissues\Domain\Model\Issue', $issue);
        $this->assertEquals('a', $issue->getTitle());
        $this->assertEquals('b', $issue->getDescription());
    }

    public function testAssigns()
    {
        $repo = new InMemoryRepository(array(
            1 => array(
                'number' => 1,
                'title' => 'a',
                'description' => 'b'
            )
        ));

        $repo->assign(new Number(1), new User('jim'));

        $issues = $repo->getIssues();
        $this->assertEquals('jim', $issues[1]['assignee']);
    }
}
