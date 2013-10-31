<?php

namespace Qissues\Tests\Console\Input;

use Qissues\Model\Issue;
use Qissues\Model\Meta\Status;
use Qissues\Model\Tracker\IssueTracker;
use Qissues\Console\Input\ExternalIssueFactory;

class ExternalIssueFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateForTracker()
    {
        $template = 'enter input here';
        $content = 'user input';
        $parsed = array('user input');
        $fields = array('title' => 'hello');

        $fileFormat = $this->getMockBuilder('Qissues\Console\Input\FileFormat')->disableOriginalConstructor()->getMock();
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

        $issueFactory = new ExternalIssueFactory($editor, $fileFormat);
        $issue = $issueFactory->createForTracker($tracker);

        $this->assertInstanceOf('Qissues\Model\Posting\NewIssue', $issue);
    }

    public function testUpdateForTracker()
    {
        $template = 'enter input here';
        $content = 'user input';
        $parsed = array('user input');
        $fields = array('title' => 'hello');

        $issue = new Issue(1, 'title', 'desc', new Status('open'), new \DateTime, new \DateTime);

        $fileFormat = $this->getMockBuilder('Qissues\Console\Input\FileFormat')->disableOriginalConstructor()->getMock();
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

        $issueFactory = new ExternalIssueFactory($editor, $fileFormat);
        $issue = $issueFactory->updateForTracker($tracker, $issue);

        $this->assertInstanceOf('Qissues\Model\Posting\NewIssue', $issue);
    }
}
