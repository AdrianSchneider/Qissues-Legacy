<?php

namespace Qissues\Interfaces\Console\Input\Strategy\Transition;

use Qissues\Domain\Workflow\TransitionDetails;
use Qissues\Domain\Workflow\TransitionRequirements;
use Qissues\Interfaces\Console\Input\Strategy\Transition\EditStrategy;

class EditStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testCreatesFromEditedContent()
    {
        $template = 'enter input here';
        $content = 'user input';
        $parsed = array('user' => 'input');
        $fields = array('resolution' => 'fixed', 'comment' => '');

        $fileFormat = $this->getMockBuilder('Qissues\Interfaces\Console\Input\FileFormats\FileFormat')->disableOriginalConstructor()->getMock();
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

        $editor = $this->getMockBuilder('Qissues\Interfaces\Console\Input\ExternalFileEditor')->disableOriginalConstructor()->getMock();
        $editor
            ->expects($this->once())
            ->method('getEdited')
            ->with($template)
            ->will($this->returnValue($content))
        ;

        $requirements = new TransitionRequirements($fields);

        $issueFactory = new EditStrategy($editor, $fileFormat);
        $details = $issueFactory->create($requirements);
        $rawDetails = $details->getDetails();

        $this->assertInstanceOf('Qissues\Model\Workflow\TransitionDetails', $details);
        $this->assertEquals('input', $rawDetails['user']);
    }

    public function testCreatesEmptyIfNoFields()
    {
        $issueFactory = new EditStrategy(
            $this->getMockBuilder('Qissues\Interfaces\Console\Input\ExternalFileEditor')->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder('Qissues\Interfaces\Console\Input\FileFormats\FileFormat')->disableOriginalConstructor()->getMock()
        );

        $details = $issueFactory->create(new TransitionRequirements(array()));

        $this->assertEmpty($details->getDetails());
        $this->assertInstanceOf('Qissues\Model\Workflow\TransitionDetails', $details);
    }

    public function testCreatesEmptyIfNoContent()
    {
        $template = 'enter input here';
        $content = '';
        $fields = array('resolution' => 'fixed', 'comment' => '');

        $fileFormat = $this->getMockBuilder('Qissues\Interfaces\Console\Input\FileFormats\FileFormat')->disableOriginalConstructor()->getMock();
        $fileFormat
            ->expects($this->once())
            ->method('seed')
            ->with($fields)
            ->will($this->returnValue($template))
        ;

        $editor = $this->getMockBuilder('Qissues\Interfaces\Console\Input\ExternalFileEditor')->disableOriginalConstructor()->getMock();
        $editor
            ->expects($this->once())
            ->method('getEdited')
            ->with($template)
            ->will($this->returnValue($content))
        ;

        $issueFactory = new EditStrategy($editor, $fileFormat);
        $details = $issueFactory->create(new TransitionRequirements($fields));

        $this->assertEmpty($details->getDetails());
        $this->assertInstanceOf('Qissues\Model\Workflow\TransitionDetails', $details);
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
