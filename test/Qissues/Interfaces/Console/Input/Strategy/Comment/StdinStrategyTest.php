<?php

namespace Qissues\Tests\Console\Input\Strategy\Comment;

use Qissues\Interfaces\Console\Input\Strategy\Comment\StdinStrategy;

class StdinStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testCreatesCommentFromStream()
    {
        $strategy = new StdinStrategy('data://text/plain,hello-world');
        $tracker = $this->getMockBuilder('Qissues\Application\Tracker\IssueTracker')->disableOriginalConstructor()->getMock();
        $comment = $strategy->createNew($tracker);

        $this->assertInstanceOf('Qissues\Domain\Model\Message', $comment);
        $this->assertEquals('hello-world', $comment->getMessage());
    }

    public function testIgnoresEmptyStreams()
    {
        $strategy = new StdinStrategy('data://text/plain,');
        $tracker = $this->getMockBuilder('Qissues\Application\Tracker\IssueTracker')->disableOriginalConstructor()->getMock();
        $comment = $strategy->createNew($tracker);

        $this->assertNull($comment);
    }

    public function testInit()
    {
        $strategy = new StdinStrategy();
        $strategy->init(
            $this->getMock('Symfony\Component\Console\Input\InputInterface'),
            $this->getMock('Symfony\Component\Console\Output\OutputInterface'),
            $this->getMockBuilder('Symfony\Component\Console\Application')->disableOriginalConstructor()->getMock()
        );
    }
}
