<?php

namespace Qissues\Tests\Console\Input\Strategy\Issue;

use Qissues\Trackers\Shared\IssueTracker;
use Qissues\Interfaces\Console\Input\Strategy\Issue\StdinStrategy;

class StdinStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateNewIssue()
    {
        $template = 'enter input here';
        $content = 'hello';
        $parsed = array('user input');
        $fields = array('title' => 'hello');
        $stream = 'data://text/plain,hello';

        $fileFormat = $this->getMockBuilder('Qissues\Interfaces\Console\Input\FileFormats\FileFormat')->disableOriginalConstructor()->getMock();
        $fileFormat
            ->expects($this->once())
            ->method('parse')
            ->with($content)
            ->will($this->returnValue($parsed))
        ;

        $tracker = new IssueTracker(
            $repository = $this->getMock('Qissues\Domain\Model\IssueRepository'),
            $mapping    = $this->getMock('Qissues\Trackers\Shared\FieldMapping'),
            $features   = $this->getMock('Qissues\Trackers\Shared\Support\FeatureSet'),
            $workflow   = $this->getMock('Qissues\Domain\Workflow\Workflow')
        );

        $mapping
            ->expects($this->once())
            ->method('toNewIssue')
            ->with($parsed)
            ->will($this->returnValue(
                $this->getMockBuilder('Qissues\Domain\Model\NewIssue')->disableOriginalConstructor()->getMock())
            )
        ;

        $issueFactory = new StdinStrategy($stream, $fileFormat);
        $issue = $issueFactory->createNew($tracker);

        $this->assertInstanceOf('Qissues\Domain\Model\NewIssue', $issue);
    }

    public function testUpdateExistingCallsCreateNew()
    {
        $template = 'enter input here';
        $content = 'hello';
        $parsed = array('user input');
        $fields = array('title' => 'hello');
        $stream = 'data://text/plain,hello';

        $fileFormat = $this->getMockBuilder('Qissues\Interfaces\Console\Input\FileFormats\FileFormat')->disableOriginalConstructor()->getMock();
        $fileFormat
            ->expects($this->once())
            ->method('parse')
            ->with($content)
            ->will($this->returnValue($parsed))
        ;

        $tracker = new IssueTracker(
            $repository = $this->getMock('Qissues\Domain\Model\IssueRepository'),
            $mapping    = $this->getMock('Qissues\Trackers\Shared\FieldMapping'),
            $features   = $this->getMock('Qissues\Trackers\Shared\Support\FeatureSet'),
            $workflow   = $this->getMock('Qissues\Domain\Workflow\Workflow')
        );

        $mapping
            ->expects($this->once())
            ->method('toNewIssue')
            ->with($parsed)
            ->will($this->returnValue(
                $this->getMockBuilder('Qissues\Domain\Model\NewIssue')->disableOriginalConstructor()->getMock())
            )
        ;

        $issueFactory = new StdinStrategy($stream, $fileFormat);
        $issue = $issueFactory->updateExisting($tracker, $this->getMockBuilder('Qissues\Domain\Model\Issue')->disableOriginalConstructor()->getMock());

        $this->assertInstanceOf('Qissues\Domain\Model\NewIssue', $issue);
    }

    public function testInitIsIgnored()
    {
        $fileFormat = $this->getMockBuilder('Qissues\Interfaces\Console\Input\FileFormats\FileFormat')->disableOriginalConstructor()->getMock();
        $strategy = new StdinStrategy('', $fileFormat);
        $strategy->init(
            $this->getMock('Symfony\Component\Console\Input\InputInterface'),
            $this->getMock('Symfony\Component\Console\Output\OutputInterface'),
            $this->getMockBuilder('Symfony\Component\Console\Application')->disableOriginalConstructor()->getMock()
        );
    }
}
