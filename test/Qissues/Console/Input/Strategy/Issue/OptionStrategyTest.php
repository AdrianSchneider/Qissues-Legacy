<?php

namespace Qissues\Tests\Console\Input\Strategy\Issue;

use Qissues\Domain\Tracker\IssueTracker;
use Qissues\Interfaces\Console\Input\Strategy\Issue\OptionStrategy;

class OptionStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateNewsFromDataOption()
    {
        $data = array(
            'title=Hello',
            'description=World'
        );
        
        $strategy = new OptionStrategy();
        $strategy->init(
            $input = $this->getMock('Symfony\Component\Console\Input\InputInterface'),
            $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface'),
            $application = $this->getMockBuilder('Symfony\Component\Console\Application')->disableOriginalConstructor()->getMock()
        );

        $tracker = new IssueTracker(
            $repository = $this->getMock('Qissues\Domain\Model\IssueRepository'),
            $mapping = $this->getMock('Qissues\Domain\Tracker\FieldMapping'),
            $features = $this->getMock('Qissues\Domain\Tracker\Support\FeatureSet'),
            $workflow = $this->getMock('Qissues\Domain\Workflow\Workflow')
        );

        $input
            ->expects($this->once())
            ->method('getOption')
            ->with('data')
            ->will($this->returnValue($data))
        ;

        $mapping
            ->expects($this->once())
            ->method('toNewIssue')
            ->with(array(
                'title' => 'Hello',
                'description' => 'World'
            ))
            ->will($this->returnValue($this->getMockBuilder('Qissues\Domain\Model\NewIssue')->disableOriginalConstructor()->getMock()))
        ;

        $issue = $strategy->createNew($tracker);
    }

    public function testUpdateExisting()
    {
        $data = array(
            'title=Hello',
            'description=World'
        );
        
        $strategy = new OptionStrategy();
        $strategy->init(
            $input = $this->getMock('Symfony\Component\Console\Input\InputInterface'),
            $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface'),
            $application = $this->getMockBuilder('Symfony\Component\Console\Application')->disableOriginalConstructor()->getMock()
        );

        $tracker = new IssueTracker(
            $repository = $this->getMock('Qissues\Domain\Model\IssueRepository'),
            $mapping = $this->getMock('Qissues\Domain\Tracker\FieldMapping'),
            $features = $this->getMock('Qissues\Domain\Tracker\Support\FeatureSet'),
            $workflow = $this->getMock('Qissues\Domain\Workflow\Workflow')
        );

        $input
            ->expects($this->once())
            ->method('getOption')
            ->with('data')
            ->will($this->returnValue($data))
        ;

        $mapping
            ->expects($this->once())
            ->method('toNewIssue')
            ->with(array(
                'title' => 'Hello',
                'description' => 'World'
            ))
            ->will($this->returnValue($this->getMockBuilder('Qissues\Domain\Model\NewIssue')->disableOriginalConstructor()->getMock()))
        ;

        $issue = $strategy->updateExisting($tracker, $this->getMockBuilder('Qissues\Domain\Model\Issue')->disableOriginalConstructor()->getMock());
    }
}
