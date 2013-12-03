<?php

namespace Qissues\Interfaces\Console\Input\Strategy\Transition;

use Qissues\Domain\Shared\Details;
use Qissues\Domain\Shared\ExpectedDetail;
use Qissues\Domain\Shared\ExpectedDetails;
use Qissues\Interfaces\Console\Input\Strategy\Transition\EditStrategy;

class EditStrategyTest extends \PHPUnit_Framework_TestCase
{
    public function testCreatesFromEditedContent()
    {
        $template = 'enter input here';
        $content = 'user input';
        $parsed = new Details(array('user' => 'input'));

        $expectations = new ExpectedDetails(array(
            new ExpectedDetail('resolution', true, 'fixed'),
            new ExpectedDetail('comment')
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

        $issueFactory = new EditStrategy($editor, $fileFormat);
        $details = $issueFactory->create($expectations);
        $rawDetails = $details->getDetails();

        $this->assertInstanceOf('Qissues\Domain\Shared\Details', $details);
        $this->assertEquals('input', $rawDetails['user']);
    }

    public function testCreatesEmptyIfNoFields()
    {
        $issueFactory = new EditStrategy(
            $this->getMockBuilder('Qissues\Interfaces\Console\Input\ExternalFileEditor')->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder('Qissues\Interfaces\Console\Input\FileFormats\FileFormat')->disableOriginalConstructor()->getMock()
        );

        $details = $issueFactory->create(new ExpectedDetails(array()));

        $this->assertEmpty($details->getDetails());
        $this->assertInstanceOf('Qissues\Domain\Shared\Details', $details);
    }

    public function testCreatesEmptyIfNoContent()
    {
        $template = 'enter input here';
        $content = '';
        $expectations = new ExpectedDetails(array(
            new ExpectedDetail('resolution', true, 'fixed'),
            new ExpectedDetail('comment')
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

        $issueFactory = new EditStrategy($editor, $fileFormat);
        $details = $issueFactory->create($expectations);

        $this->assertEmpty($details->getDetails());
        $this->assertInstanceOf('Qissues\Domain\Shared\Details', $details);
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
