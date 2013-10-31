<?php

namespace Qissues\Tests\Console\Input;

use Qissues\Model\Tracker\IssueTracker;
use Qissues\Console\Input\ExternalIssueFactory;

class ExternalIssueFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateForTracker()
    {
        $template = 'enter input here';
        $content = 'user input';
        $parsed = array('user input');

        $parser = $this->getMockBuilder('Qissues\Console\Input\FrontMatterParser')->disableOriginalConstructor()->getMock();
        $parser
            ->expects($this->once())
            ->method('parse')
            ->with($content)
            ->will($this->returnValue($parsed))
        ;

        $editor = $this->getMockBuilder('Qissues\Console\Input\ExternalFileEditor')->disableOriginalConstructor()->getMock();
        $editor
            ->expects($this->once())
            ->method('getEdited')
            ->with($this->stringContains('title: '))
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
            ->will($this->returnValue($fields = array(
                'title' => 'title',
                'description' => ''
            )));
        $mapping
            ->expects($this->once())
            ->method('toNewIssue')
            ->with($parsed)
            ->will($this->returnValue(
                $this->getMockBuilder('Qissues\Model\Posting\NewIssue')->disableOriginalConstructor()->getMock())
            );

        $issueFactory = new ExternalIssueFactory($editor, $parser);
        $issue = $issueFactory->createForTracker($tracker);

        $this->assertInstanceOf('Qissues\Model\Posting\NewIssue', $issue);
    }
}
