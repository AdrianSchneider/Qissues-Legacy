<?php

namespace Qissues\Tests\Console\Input\Strategy\Comment;

use Qissues\Interfaces\Console\Input\Strategy\Comment\OptionStrategy;

class OptionStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnsPopulatedMessage()
    {
        $strategy = new OptionStrategy();
        $strategy->init(
            $input = $this->getMock('Symfony\Component\Console\Input\InputInterface'),
            $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface'),
            $this->getMockBuilder('Symfony\Component\Console\Application')->disableOriginalConstructor()->getMock()
        );

        $input
            ->expects($this->once())
            ->method('getOption')
            ->with('message')
            ->will($this->returnValue($message = 'hello world'))
        ;

        $comment = $strategy->createNew($this->getMockBuilder('Qissues\Application\Tracker\IssueTracker')->disableOriginalConstructor()->getMock());

        $this->assertInstanceOf('Qissues\Domain\Model\Message', $comment);
        $this->assertEquals('hello world', $comment->getMessage());
    }

    public function testReturnsNullWhenEmpty()
    {
        $strategy = new OptionStrategy();
        $strategy->init(
            $input = $this->getMock('Symfony\Component\Console\Input\InputInterface'),
            $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface'),
            $this->getMockBuilder('Symfony\Component\Console\Application')->disableOriginalConstructor()->getMock()
        );

        $input
            ->expects($this->once())
            ->method('getOption')
            ->with('message')
            ->will($this->returnValue(''))
        ;

        $comment = $strategy->createNew($this->getMockBuilder('Qissues\Application\Tracker\IssueTracker')->disableOriginalConstructor()->getMock());

        $this->assertNull($comment);
    }
}
