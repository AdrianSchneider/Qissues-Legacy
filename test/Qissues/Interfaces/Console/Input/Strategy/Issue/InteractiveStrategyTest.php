<?php

namespace Qissues\Tests\Console\Input\Strategy\Issue;

use Qissues\Application\Tracker\IssueTracker;
use Qissues\Interfaces\Console\Input\Strategy\Issue\InteractiveStrategy;
use Symfony\Component\Console\Input\ArrayInput;

class InteractiveStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testThrowsExceptionIfNotInteractive()
    {
        $this->setExpectedException('RunTimeException', 'interactive');

        $input = new ArrayInput(array());
        $input->setInteractive(false);
        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
        $application = $this->getApplication();

        $strategy = new InteractiveStrategy();
        $strategy->init($input, $output, $application);
    }

    public function testInitThrowsExceptionOnSecondRun()
    {
        $this->setExpectedException('BadMethodCallException');

        $input = new ArrayInput(array());
        $input->setInteractive(true);
        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
        $application = $this->getApplication();

        $strategy = new InteractiveStrategy();
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

        $strategy = new InteractiveStrategy();
        $strategy->init($input, $output, $application);

        $tracker = new IssueTracker(
            $repository = $this->getMock('Qissues\Domain\Model\IssueRepository'),
            $mapping = $this->getMock('Qissues\Application\Tracker\FieldMapping'),
            $features = $this->getMock('Qissues\Application\Tracker\Support\FeatureSet'),
            $workflow = $this->getMock('Qissues\Domain\Model\Workflow')
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
            ->will($this->returnValue($this->getMockBuilder('Qissues\Domain\Model\Request\NewIssue')->disableOriginalConstructor()->getMock()))
        ;

        $issue = $strategy->createNew($tracker);
        $this->assertInstanceOf('Qissues\Domain\Model\Request\NewIssue', $issue);
    }

    public function testUpdateExisting()
    {
        $issue = $this->getMockBuilder('Qissues\Domain\Model\Issue')->disableOriginalConstructor()->getMock();
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

        $strategy = new InteractiveStrategy();
        $strategy->init($input, $output, $application);

        $tracker = new IssueTracker(
            $repository = $this->getMock('Qissues\Domain\Model\IssueRepository'),
            $mapping = $this->getMock('Qissues\Application\Tracker\FieldMapping'),
            $features = $this->getMock('Qissues\Application\Tracker\Support\FeatureSet'),
            $workflow = $this->getMock('Qissues\Domain\Model\Workflow')
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
            ->will($this->returnValue($this->getMockBuilder('Qissues\Domain\Model\Request\NewIssue')->disableOriginalConstructor()->getMock()))
        ;

        $issue = $strategy->updateExisting($tracker, $issue);
        $this->assertInstanceOf('Qissues\Domain\Model\Request\NewIssue', $issue);
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
