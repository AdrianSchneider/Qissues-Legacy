<?php

namespace Qissues\Tests\Console\Input\Strategy\Issue;

use Qissues\Domain\Model\Issue;
use Qissues\Domain\Shared\Status;
use Qissues\Domain\Shared\Details;
use Qissues\Domain\Shared\ExpectedDetail;
use Qissues\Domain\Shared\ExpectedDetails;
use Qissues\Application\Tracker\IssueTracker;
use Qissues\Interfaces\Console\Input\Strategy\Issue\EditStrategy;

class EditStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateNew()
    {
        $template = 'enter input here';
        $content = 'user input';
        $parsed = new Details($parsedRaw = array('user' => 'input'));
        $expectations = new ExpectedDetails(array(
            new ExpectedDetail('user', true, 'input')
        ));

        $fileFormat = $this->getMockBuilder('Qissues\Interfaces\Console\Input\FileFormats\FileFormat')->disableOriginalConstructor()->getMock();
        $fileFormat
            ->expects($this->once())
            ->method('seed')
            ->with($expectations)
            ->will($this->returnValue($template))
        ;
        $fileFormat
            ->expects($this->once())
            ->method('parse')
            ->with($content)
            ->will($this->returnValue($parsed))
        ;

        $editor = $this->getMockBuilder('Qissues\Interfaces\Console\Input\ExternalFileEditor')->disableOriginalConstructor()->getMock();
        $editor
            ->expects($this->once())
            ->method('getEdited')
            ->with($template)
            ->will($this->returnValue($content))
        ;

        $tracker = new IssueTracker(
            $repository = $this->getMock('Qissues\Domain\Model\IssueRepository'),
            $mapping    = $this->getMock('Qissues\Application\Tracker\FieldMapping'),
            $features   = $this->getMock('Qissues\Application\Tracker\Support\FeatureSet'),
            $workflow   = $this->getMock('Qissues\Domain\Model\Workflow')
        );

        $mapping
            ->expects($this->once())
            ->method('getExpectedDetails')
            ->will($this->returnValue($expectations))
        ;
        $mapping
            ->expects($this->once())
            ->method('toNewIssue')
            ->with($parsedRaw)
            ->will($this->returnValue(
                $this->getMockBuilder('Qissues\Domain\Model\Request\NewIssue')->disableOriginalConstructor()->getMock())
            )
        ;

        $strategy = new EditStrategy($editor, $fileFormat);
        $strategy->init(
            $this->getMock('Symfony\Component\Console\Input\InputInterface'),
            $this->getMock('Symfony\Component\Console\Output\OutputInterface'),
            $this->getMockBuilder('Symfony\Component\Console\Application')->disableOriginalConstructor()->getMock()
        );
        $issue = $strategy->createNew($tracker);

        $this->assertInstanceOf('Qissues\Domain\Model\Request\NewIssue', $issue);
    }

    public function testCreateWithViolationTriesAgain()
    {
        $template = 'enter input here';
        $content = array('incorrect input', 'correct input');
        $parsedRaw = array(array('bad' => 'value'), array('user' => 'input'));
        $details = array(new Details($parsedRaw[0]), new Details($parsedRaw[1]));
        $expectations = new ExpectedDetails(array( new ExpectedDetail('user', true, 'input')));


        $fileFormat = $this->getMockBuilder('Qissues\Interfaces\Console\Input\FileFormats\FileFormat')->disableOriginalConstructor()->getMock();
        $fileFormat
            ->expects($this->once())
            ->method('seed')
            ->with($expectations)
            ->will($this->returnValue($template))
        ;

        // first pass
        $editor = $this->getMockBuilder('Qissues\Interfaces\Console\Input\ExternalFileEditor')->disableOriginalConstructor()->getMock();
        $editor
            ->expects($this->at(0))
            ->method('getEdited')
            ->with($template)
            ->will($this->returnValue($content[0]))
        ;
        $fileFormat
            ->expects($this->at(1))
            ->method('parse')
            ->with($content[0])
            ->will($this->returnValue($details[0]))
        ;


        // second pass
        $editor
            ->expects($this->at(1))
            ->method('getEdited')
            ->with($content[0])
            ->will($this->returnValue($content[1]))
        ;
        $fileFormat
            ->expects($this->at(2))
            ->method('parse')
            ->with($content[1])
            ->will($this->returnValue($details[1]))
        ;

        $tracker = new IssueTracker(
            $repository = $this->getMock('Qissues\Domain\Model\IssueRepository'),
            $mapping    = $this->getMock('Qissues\Application\Tracker\FieldMapping'),
            $features   = $this->getMock('Qissues\Application\Tracker\Support\FeatureSet'),
            $workflow   = $this->getMock('Qissues\Domain\Model\Workflow')
        );

        $mapping
            ->expects($this->once())
            ->method('getExpectedDetails')
            ->will($this->returnValue($expectations))
        ;
        $mapping
            ->expects($this->once())
            ->method('toNewIssue')
            ->with($parsedRaw[1])
            ->will($this->returnValue(
                $this->getMockBuilder('Qissues\Domain\Model\Request\NewIssue')->disableOriginalConstructor()->getMock())
            )
        ;

        $strategy = new EditStrategy($editor, $fileFormat, 0.0001);
        $strategy->init(
            $this->getMock('Symfony\Component\Console\Input\InputInterface'),
            $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface'),
            $this->getMockBuilder('Symfony\Component\Console\Application')->disableOriginalConstructor()->getMock()
        );

        $output
            ->expects($this->atLeastOnce())
            ->method('writeln')
            ->with($this->callback(function($msg) {
                return strpos($msg, 'error') !== false;
            }))
        ;

        $issue = $strategy->createNew($tracker);
        $this->assertInstanceOf('Qissues\Domain\Model\Request\NewIssue', $issue);
    }

    public function testCreateNewReturnsNullIfNoContent()
    {
        $template = 'enter input here';
        $content = '';
        $parsed = new Details($parsedRaw = array('user' => 'input'));
        $expectations = new ExpectedDetails(array(
            new ExpectedDetail('user', true, 'input')
        ));

        $fileFormat = $this->getMockBuilder('Qissues\Interfaces\Console\Input\FileFormats\FileFormat')->disableOriginalConstructor()->getMock();
        $fileFormat
            ->expects($this->once())
            ->method('seed')
            ->with($expectations)
            ->will($this->returnValue($template))
        ;

        $editor = $this->getMockBuilder('Qissues\Interfaces\Console\Input\ExternalFileEditor')->disableOriginalConstructor()->getMock();
        $editor
            ->expects($this->once())
            ->method('getEdited')
            ->with($template)
            ->will($this->returnValue($content))
        ;

        $tracker = new IssueTracker(
            $repository = $this->getMock('Qissues\Domain\Model\IssueRepository'),
            $mapping    = $this->getMock('Qissues\Application\Tracker\FieldMapping'),
            $features   = $this->getMock('Qissues\Application\Tracker\Support\FeatureSet'),
            $workflow   = $this->getMock('Qissues\Domain\Model\Workflow')
        );

        $mapping
            ->expects($this->once())
            ->method('getExpectedDetails')
            ->will($this->returnValue($expectations))
        ;

        $issueFactory = new EditStrategy($editor, $fileFormat);
        $issue = $issueFactory->createNew($tracker);

        $this->assertNull($issue);
    }

    public function testUpdateExisting()
    {
        $issue = new Issue(1, 'title', 'desc', new Status('open'), new \DateTime, new \DateTime);

        $template = 'enter input here';
        $content = 'user input';
        $parsed = new Details($parsedRaw = array('user' => 'input'));
        $expectations = new ExpectedDetails(array(
            new ExpectedDetail('user', true, 'input')
        ));

        $fileFormat = $this->getMockBuilder('Qissues\Interfaces\Console\Input\FileFormats\FileFormat')->disableOriginalConstructor()->getMock();
        $fileFormat
            ->expects($this->once())
            ->method('seed')
            ->with($expectations)
            ->will($this->returnValue($template))
        ;
        $fileFormat
            ->expects($this->once())
            ->method('parse')
            ->with($content)
            ->will($this->returnValue($parsed))
        ;

        $editor = $this->getMockBuilder('Qissues\Interfaces\Console\Input\ExternalFileEditor')->disableOriginalConstructor()->getMock();
        $editor
            ->expects($this->once())
            ->method('getEdited')
            ->with($template)
            ->will($this->returnValue($content))
        ;

        $tracker = new IssueTracker(
            $repository = $this->getMock('Qissues\Domain\Model\IssueRepository'),
            $mapping    = $this->getMock('Qissues\Application\Tracker\FieldMapping'),
            $features   = $this->getMock('Qissues\Application\Tracker\Support\FeatureSet'),
            $workflow   = $this->getMock('Qissues\Domain\Model\Workflow')
        );

        $mapping
            ->expects($this->once())
            ->method('getExpectedDetails')
            ->with($issue)
            ->will($this->returnValue($expectations))
        ;
        $mapping
            ->expects($this->once())
            ->method('toNewIssue')
            ->with($parsedRaw)
            ->will($this->returnValue(
                $this->getMockBuilder('Qissues\Domain\Model\Request\NewIssue')->disableOriginalConstructor()->getMock())
            )
        ;

        $issueFactory = new EditStrategy($editor, $fileFormat);
        $issue = $issueFactory->updateExisting($tracker, $issue);

        $this->assertInstanceOf('Qissues\Domain\Model\Request\NewIssue', $issue);
    }

    public function testInit()
    {
        $editor = $this->getMockBuilder('Qissues\Interfaces\Console\Input\ExternalFileEditor')->disableOriginalConstructor()->getMock();
        $fileFormat = $this->getMockBuilder('Qissues\Interfaces\Console\Input\FileFormats\FileFormat')->disableOriginalConstructor()->getMock();

        $strategy = new EditStrategy($editor, $fileFormat);
        $strategy->init(
            $this->getMock('Symfony\Component\Console\Input\InputInterface'),
            $this->getMock('Symfony\Component\Console\Output\OutputInterface'),
            $this->getMockBuilder('Symfony\Component\Console\Application')->disableOriginalConstructor()->getMock()
        );
    }
}
