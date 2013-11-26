<?php

namespace Qissues\Console\Input\Strategy\Transition;

use Qissues\Model\Workflow\TransitionDetails;
use Qissues\Model\Workflow\TransitionRequirements;
use Qissues\Console\Input\Strategy\Transition\EditStrategy;

class EditStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $template = 'enter input here';
        $content = 'user input';
        $parsed = array('user' => 'input');
        $fields = array('resolution' => 'fixed', 'comment' => '');

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

        $requirements = new TransitionRequirements($fields);

        $issueFactory = new EditStrategy($editor, $fileFormat);
        $details = $issueFactory->create($requirements);
        $rawDetails = $details->getDetails();

        $this->assertInstanceOf('QIssues\Model\Workflow\TransitionDetails', $details);
        $this->assertEquals('input', $rawDetails['user']);
    }

    public function testInit()
    {
        $editor = $this->getMockBuilder('Qissues\Console\Input\ExternalFileEditor')->disableOriginalConstructor()->getMock();
        $fileFormat = $this->getMockBuilder('Qissues\Console\Input\FileFormats\FileFormat')->disableOriginalConstructor()->getMock();

        $strategy = new EditStrategy($editor, $fileFormat);
        $strategy->init(
            $this->getMock('Symfony\Component\Console\Input\InputInterface'),
            $this->getMock('Symfony\Component\Console\Output\OutputInterface'),
            $this->getMockBuilder('Symfony\Component\Console\Application')->disableOriginalConstructor()->getMock()
        );
    }
}
