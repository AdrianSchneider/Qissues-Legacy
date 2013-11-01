<?php

namespace Qissues\Tests\Console\Input\Strategy\Comment;

use Qissues\Console\Input\Strategy\Comment\EditStrategy;

class EditStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnsPopulatedNewComment()
    {
        $editor = $this->getMockBuilder('Qissues\Console\Input\ExternalFileEditor')->disableOriginalConstructor()->getMock();
        $editor
            ->expects($this->once())
            ->method('getEdited')
            ->with('')
            ->will($this->returnValue('content'))
        ;

        $strategy = new EditStrategy($editor);
        $comment = $strategy->createNew($this->getMockBuilder('Qissues\Model\Tracker\IssueTracker')->disableOriginalConstructor()->getMock());

        $this->assertInstanceOf('Qissues\Model\Posting\NewComment', $comment);
        $this->assertEquals('content', $comment->getMessage());
    }

    public function testReturnsNullWhenEmpty()
    {
        $editor = $this->getMockBuilder('Qissues\Console\Input\ExternalFileEditor')->disableOriginalConstructor()->getMock();
        $editor
            ->expects($this->once())
            ->method('getEdited')
            ->with('')
            ->will($this->returnValue(''))
        ;

        $strategy = new EditStrategy($editor);
        $comment = $strategy->createNew($this->getMockBuilder('Qissues\Model\Tracker\IssueTracker')->disableOriginalConstructor()->getMock());

        $this->assertNull($comment);
    }
}
