<?php

namespace Qissues\Tests\Console\Input\Strategy\Issue;

use Qissues\Application\Tracker\IssueTracker;
use Qissues\Domain\Shared\ExpectedDetail;
use Qissues\Domain\Shared\ExpectedDetails;
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
            $mapping = $this->getMock('Qissues\Application\Tracker\FieldMapping'),
            $features = $this->getMock('Qissues\Application\Tracker\Support\FeatureSet'),
            $workflow = $this->getMock('Qissues\Domain\Model\Workflow')
        );

        $input
            ->expects($this->once())
            ->method('getOption')
            ->with('data')
            ->will($this->returnValue($data))
        ;

        $details = new ExpectedDetails(array(
            new ExpectedDetail('field', true, 'value')
        ));

        $mapping
            ->expects($this->once())
            ->method('toNewIssue')
            ->with(array(
                'title' => 'Hello',
                'description' => 'World',
                'field' => 'value'
            ))
            ->will($this->returnValue($this->getMockBuilder('Qissues\Domain\Model\Request\NewIssue')->disableOriginalConstructor()->getMock()))
        ;
        $mapping
            ->expects($this->once())
            ->method('getExpectedDetails')
            ->with(null)
            ->will($this->returnValue($details))
        ;

        $issue = $strategy->createNew($tracker);
    }

    public function testUpdateExisting()
    {
        $data = array(
            'title=Hello',
            'description=World'
        );

        $originalIssue = $this->getMockBuilder('Qissues\Domain\Model\Issue')->disableOriginalConstructor()->getMock();
        
        $strategy = new OptionStrategy();
        $strategy->init(
            $input = $this->getMock('Symfony\Component\Console\Input\InputInterface'),
            $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface'),
            $application = $this->getMockBuilder('Symfony\Component\Console\Application')->disableOriginalConstructor()->getMock()
        );

        $tracker = new IssueTracker(
            $repository = $this->getMock('Qissues\Domain\Model\IssueRepository'),
            $mapping = $this->getMock('Qissues\Application\Tracker\FieldMapping'),
            $features = $this->getMock('Qissues\Application\Tracker\Support\FeatureSet'),
            $workflow = $this->getMock('Qissues\Domain\Model\Workflow')
        );

        $input
            ->expects($this->once())
            ->method('getOption')
            ->with('data')
            ->will($this->returnValue($data))
        ;

        $details = new ExpectedDetails(array(
            new ExpectedDetail('field', true, 'value')
        ));

        $mapping
            ->expects($this->once())
            ->method('toNewIssue')
            ->with(array(
                'title' => 'Hello',
                'description' => 'World',
                'field' => 'value'
            ))
            ->will($this->returnValue($this->getMockBuilder('Qissues\Domain\Model\Request\NewIssue')->disableOriginalConstructor()->getMock()))
        ;
        $mapping
            ->expects($this->once())
            ->method('getExpectedDetails')
            ->with($originalIssue)
            ->will($this->returnValue($details))
        ;

        $issue = $strategy->updateExisting($tracker, $originalIssue);
    }
}
