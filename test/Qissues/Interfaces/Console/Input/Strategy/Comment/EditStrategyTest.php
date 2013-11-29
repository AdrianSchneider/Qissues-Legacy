<?php

namespace Qissues\Tests\Console\Input\Strategy\Comment;

use Qissues\Interfaces\Console\Input\Strategy\Comment\EditStrategy;

class EditStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnsPopulatedNewComment()
    {
        $editor = $this->getMockBuilder('Qissues\Interfaces\Console\Input\ExternalFileEditor')->disableOriginalConstructor()->getMock();
        $editor
            ->expects($this->once())
            ->method('getEdited')
            ->with('')
            ->will($this->returnValue('content'))
        ;

        $strategy = new EditStrategy($editor);
        $comment = $strategy->createNew($this->getMockBuilder('Qissues\Trackers\Shared\IssueTracker')->disableOriginalConstructor()->getMock());

        $this->assertInstanceOf('Qissues\Domain\Model\Request\NewComment', $comment);
        $this->assertEquals('content', $comment->getMessage());
    }

    public function testReturnsNullWhenEmpty()
    {
        $editor = $this->getMockBuilder('Qissues\Interfaces\Console\Input\ExternalFileEditor')->disableOriginalConstructor()->getMock();
        $editor
            ->expects($this->once())
            ->method('getEdited')
            ->with('')
            ->will($this->returnValue(''))
        ;

        $strategy = new EditStrategy($editor);
        $comment = $strategy->createNew($this->getMockBuilder('Qissues\Trackers\Shared\IssueTracker')->disableOriginalConstructor()->getMock());

        $this->assertNull($comment);
    }

    public function testInit()
    {
        $editor = $this->getMockBuilder('Qissues\Interfaces\Console\Input\ExternalFileEditor')->disableOriginalConstructor()->getMock();
        $strategy = new EditStrategy($editor);
        $strategy->init(
            $this->getMock('Symfony\Component\Console\Input\InputInterface'),
            $this->getMock('Symfony\Component\Console\Output\OutputInterface'),
            $this->getMockBuilder('Symfony\Component\Console\Application')->disableOriginalConstructor()->getMock()
        );
    }
}
