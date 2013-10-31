<?php

namespace Qissues\Tests\Console\Input\Strategy;

use Qissues\Model\Tracker\IssueTracker;
use Qissues\Console\Input\Strategy\InteractiveIssueStrategy;
use Symfony\Component\Console\Input\ArrayInput;

class InteractiveIssueStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testThrowsExceptionIfNotInteractive()
    {
        $this->setExpectedException('RunTimeException', 'interactive');

        $input = new ArrayInput(array());
        $input->setInteractive(false);
        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
        $application = $this->getApplication();

        $strategy = new InteractiveIssueStrategy();
        $strategy->init($input, $output, $application);
    }

    public function testInitThrowsExceptionOnSecondRun()
    {
        $this->setExpectedException('BadMethodCallException');

        $input = new ArrayInput(array());
        $input->setInteractive(true);
        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
        $application = $this->getApplication();

        $strategy = new InteractiveIssueStrategy();
        $strategy->init($input, $output, $application);
        $strategy->init($input, $output, $application);
    }

    public function testCreateNew()
    {
        $fields = array('a' => 'b');

        $input = new ArrayInput(array());
        $input->setInteractive(true);
        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

        $dialog = $this->getMockBuilder('Symfony\Component\Console\Helper\DialogHelper')->disableOriginalConstructor()->getMock();
        $dialog
            ->expects($this->once())
            ->method('ask')
            ->with($output, 'a: ', 'b')
            ->will($this->returnValue('c'))
        ;

        $application = $this->getApplication($dialog);

        $strategy = new InteractiveIssueStrategy();
        $strategy->init($input, $output, $application);

        $tracker = new IssueTracker(
            $repository = $this->getMock('Qissues\Model\Tracker\IssueRepository'),
            $mapping = $this->getMock('Qissues\Model\Tracker\FieldMapping'),
            $features = $this->getMock('Qissues\Model\Tracker\Support\FeatureSet')
        );

        $mapping
            ->expects($this->once())
            ->method('getEditFields')
            ->will($this->returnValue($fields))
        ;
        $mapping
            ->expects($this->once())
            ->method('toNewIssue')
            ->with(array('a' => 'c'))
            ->will($this->returnValue($this->getMockBuilder('Qissues\Model\Posting\NewIssue')->disableOriginalConstructor()->getMock()))
        ;

        $issue = $strategy->createNew($tracker);
        $this->assertInstanceOf('Qissues\Model\Posting\NewIssue', $issue);
    }

    public function testUpdateExisting()
    {
        $issue = $this->getMockBuilder('Qissues\Model\Issue')->disableOriginalConstructor()->getMock();
        $fields = array('a' => 'b');

        $input = new ArrayInput(array());
        $input->setInteractive(true);
        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

        $dialog = $this->getMockBuilder('Symfony\Component\Console\Helper\DialogHelper')->disableOriginalConstructor()->getMock();
        $dialog
            ->expects($this->once())
            ->method('ask')
            ->with($output, 'a: ', 'b')
            ->will($this->returnValue('c'))
        ;

        $application = $this->getApplication($dialog);

        $strategy = new InteractiveIssueStrategy();
        $strategy->init($input, $output, $application);

        $tracker = new IssueTracker(
            $repository = $this->getMock('Qissues\Model\Tracker\IssueRepository'),
            $mapping = $this->getMock('Qissues\Model\Tracker\FieldMapping'),
            $features = $this->getMock('Qissues\Model\Tracker\Support\FeatureSet')
        );

        $mapping
            ->expects($this->once())
            ->method('getEditFields')
            ->with($issue)
            ->will($this->returnValue($fields))
        ;
        $mapping
            ->expects($this->once())
            ->method('toNewIssue')
            ->with(array('a' => 'c'))
            ->will($this->returnValue($this->getMockBuilder('Qissues\Model\Posting\NewIssue')->disableOriginalConstructor()->getMock()))
        ;

        $issue = $strategy->updateExisting($tracker, $issue);
        $this->assertInstanceOf('Qissues\Model\Posting\NewIssue', $issue);
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
