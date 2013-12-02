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
            new ExpectedDetail('user', 'input')
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

        $issueFactory = new EditStrategy($editor, $fileFormat);
        $issue = $issueFactory->createNew($tracker);

        $this->assertInstanceOf('Qissues\Domain\Model\Request\NewIssue', $issue);
    }

    public function testCreateNewReturnsNullIfNoContent()
    {
        $template = 'enter input here';
        $content = '';
        $parsed = new Details($parsedRaw = array('user' => 'input'));
        $expectations = new ExpectedDetails(array(
            new ExpectedDetail('user', 'input')
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
            new ExpectedDetail('user', 'input')
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
