<?php

namespace Qissues\Tests\Console\Input\Strategy;

use Qissues\Model\Issue;
use Qissues\Model\Meta\Status;
use Qissues\Model\Tracker\IssueTracker;
use Qissues\Console\Input\Strategy\EditIssueStrategy;

class EditIssueStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateNew()
    {
        $template = 'enter input here';
        $content = 'user input';
        $parsed = array('user input');
        $fields = array('title' => 'hello');

        $fileFormat = $this->getMockBuilder('Qissues\Console\Input\FileFormats\FileFormat')->disableOriginalConstructor()->getMock();
        $fileFormat
            ->expects($this->once())
            ->method('seed')
            ->with($fields)
            ->will($this->returnValue($template))
        ;
        $fileFormat
            ->expects($this->once())
            ->method('parse')
            ->with($content)
            ->will($this->returnValue($parsed))
        ;

        $editor = $this->getMockBuilder('Qissues\Console\Input\ExternalFileEditor')->disableOriginalConstructor()->getMock();
        $editor
            ->expects($this->once())
            ->method('getEdited')
            ->with($template)
            ->will($this->returnValue($content))
        ;

        $tracker = new IssueTracker(
            $repository = $this->getMock('Qissues\Model\Tracker\IssueRepository'),
            $mapping    = $this->getMock('Qissues\Model\Tracker\FieldMapping'),
            $features   = $this->getMock('Qissues\Model\Tracker\Support\FeatureSet')
        );

        $mapping
            ->expects($this->once())
            ->method('getEditFields')
            ->will($this->returnValue($fields))
        ;
        $mapping
            ->expects($this->once())
            ->method('toNewIssue')
            ->with($parsed)
            ->will($this->returnValue(
                $this->getMockBuilder('Qissues\Model\Posting\NewIssue')->disableOriginalConstructor()->getMock())
            )
        ;

        $issueFactory = new EditIssueStrategy($editor, $fileFormat);
        $issue = $issueFactory->createNew($tracker);

        $this->assertInstanceOf('Qissues\Model\Posting\NewIssue', $issue);
    }

    public function testUpdateExisting()
    {
        $template = 'enter input here';
        $content = 'user input';
        $parsed = array('user input');
        $fields = array('title' => 'hello');

        $issue = new Issue(1, 'title', 'desc', new Status('open'), new \DateTime, new \DateTime);

        $fileFormat = $this->getMockBuilder('Qissues\Console\Input\FileFormats\FileFormat')->disableOriginalConstructor()->getMock();
        $fileFormat
            ->expects($this->once())
            ->method('seed')
            ->with($fields)
            ->will($this->returnValue($template))
        ;
        $fileFormat
            ->expects($this->once())
            ->method('parse')
            ->with($content)
            ->will($this->returnValue($parsed))
        ;

        $editor = $this->getMockBuilder('Qissues\Console\Input\ExternalFileEditor')->disableOriginalConstructor()->getMock();
        $editor
            ->expects($this->once())
            ->method('getEdited')
            ->with($template)
            ->will($this->returnValue($content))
        ;

        $tracker = new IssueTracker(
            $repository = $this->getMock('Qissues\Model\Tracker\IssueRepository'),
            $mapping    = $this->getMock('Qissues\Model\Tracker\FieldMapping'),
            $features   = $this->getMock('Qissues\Model\Tracker\Support\FeatureSet')
        );

        $mapping
            ->expects($this->once())
            ->method('getEditFields')
            ->will($this->returnValue($fields))
        ;
        $mapping
            ->expects($this->once())
            ->method('toNewIssue')
            ->with($parsed)
            ->will($this->returnValue(
                $this->getMockBuilder('Qissues\Model\Posting\NewIssue')->disableOriginalConstructor()->getMock())
            )
        ;

        $issueFactory = new EditIssueStrategy($editor, $fileFormat);
        $issue = $issueFactory->updateExisting($tracker, $issue);

        $this->assertInstanceOf('Qissues\Model\Posting\NewIssue', $issue);
    }
}
