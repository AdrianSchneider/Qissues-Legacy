<?php

namespace Qissues\Tests\Console\Input\Strategy\Comment;

use Qissues\Domain\Tracker\IssueTracker;
use Qissues\Interfaces\Console\Input\Strategy\Comment\InteractiveStrategy;
use Symfony\Component\Console\Input\ArrayInput;

class InteractiveStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testInitThrowsExceptionIfInputIsntInteractive()
    {
        $this->setExpectedException('RunTimeException', 'interactive');

        $input = new ArrayInput(array());
        $input->setInteractive(false);

        $strategy = new InteractiveStrategy();
        $strategy->init(
            $input,
            $this->getMock('Symfony\Component\Console\Output\OutputInterface'),
            $this->getMockBuilder('Symfony\Component\Console\Application')->disableOriginalConstructor()->getMock()
        );
    }

    public function testInitThrowsExceptionIfAlreadyRun()
    {
        $this->setExpectedException('BadMethodCallException', 'once');

        $input = new ArrayInput(array());
        $input->setInteractive(true);

        $strategy = new InteractiveStrategy();
        $strategy->init(
            $input,
            $this->getMock('Symfony\Component\Console\Output\OutputInterface'),
            $this->getApplication()
        );
        $strategy->init(
            $input,
            $this->getMock('Symfony\Component\Console\Output\OutputInterface'),
            $this->getMockBuilder('Symfony\Component\Console\Application')->disableOriginalConstructor()->getMock()
        );
    }

    public function testCreateNewCreatesNewCommentFromDialog()
    {
        $input = new ArrayInput(array());
        $input->setInteractive(true);
        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

        $dialog = $this->getMockBuilder('Symfony\Component\Console\Helper\DialogHelper')->disableOriginalConstructor()->getMock();
        $dialog
            ->expects($this->once())
            ->method('ask')
            ->with($output, 'Comment: ')
            ->will($this->returnValue('hello world'))
        ;

        $strategy = new InteractiveStrategy();
        $strategy->init($input, $output, $this->getApplication($dialog));

        $tracker = new IssueTracker(
            $repository = $this->getMock('Qissues\Domain\Model\IssueRepository'),
            $mapping = $this->getMock('Qissues\Domain\Tracker\FieldMapping'),
            $features = $this->getMock('Qissues\Domain\Tracker\Support\FeatureSet'),
            $workflow = $this->getMock('Qissues\Domain\Workflow\Workflow')
        );

        $comment = $strategy->createNew($tracker);

        $this->assertInstanceOf('Qissues\Domain\Model\NewComment', $comment);
        $this->assertEquals('hello world', $comment->getMessage());
    }

    public function testCreateNewReturnsNullIfNoInput()
    {
        $input = new ArrayInput(array());
        $input->setInteractive(true);
        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

        $dialog = $this->getMockBuilder('Symfony\Component\Console\Helper\DialogHelper')->disableOriginalConstructor()->getMock();
        $dialog
            ->expects($this->once())
            ->method('ask')
            ->with($output, 'Comment: ')
            ->will($this->returnValue(''))
        ;

        $strategy = new InteractiveStrategy();
        $strategy->init($input, $output, $this->getApplication($dialog));

        $tracker = new IssueTracker(
            $repository = $this->getMock('Qissues\Domain\Model\IssueRepository'),
            $mapping = $this->getMock('Qissues\Domain\Tracker\FieldMapping'),
            $features = $this->getMock('Qissues\Domain\Tracker\Support\FeatureSet'),
            $workflow = $this->getMock('Qissues\Domain\Workflow\Workflow')
        );

        $comment = $strategy->createNew($tracker);

        $this->assertNull($comment);
    }

    protected function getApplication($dialog = null)
    {
        if (!$dialog) {
            $dialog = $this->getMockBuilder('Symfony\Component\Console\Helper\DialogHelper')->disableOriginalConstructor()->getMock();
        }

        $helperSet = $this->getMockBuilder('Symfony\Component\Console\Helper\HelperSet')->disableOriginalConstructor()->getMock();
        $helperSet
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValue($dialog));
        ;

        $application = $this->getMockBuilder('Symfony\Component\Console\Application')->disableOriginalConstructor()->getMock();
        $application
            ->expects($this->any())
            ->method('getHelperSet')
            ->will($this->returnValue($helperSet))
        ;

        return $application;
    }
}
