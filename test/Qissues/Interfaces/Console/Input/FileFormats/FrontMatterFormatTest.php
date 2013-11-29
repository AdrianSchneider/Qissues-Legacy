<?php

namespace Qissues\Tests\Console\Input\FileFormats;

use Qissues\Interfaces\Console\Input\FileFormats\FrontMatterFormat;

class FrontMatterFormatTest extends \PHPUnit_Framework_TestCase
{
    public function testSeed()
    {
        $parser = $this->getMockBuilder('Qissues\Application\Input\FrontMatterParser')->disableOriginalConstructor()->getMock();
        $format = new FrontMatterFormat($parser);

        $template = $format->seed(array('a' => 'b'));
        $this->assertEquals("---\na: b\n---\nEnter content here...", $template);
    }

    public function testSeedDoesntDoublePrintDescription()
    {
        $parser = $this->getMockBuilder('Qissues\Application\Input\FrontMatterParser')->disableOriginalConstructor()->getMock();
        $format = new FrontMatterFormat($parser);

        $template = $format->seed(array('a' => 'b', 'description' => 'Hello World'));
        $this->assertEquals("---\na: b\n---\nHello World", $template);
    }

    public function testSpecifyAnotherDescriptionField()
    {
        $parser = $this->getMockBuilder('Qissues\Application\Input\FrontMatterParser')->disableOriginalConstructor()->getMock();
        $format = new FrontMatterFormat($parser, 'body');

        $template = $format->seed(array('a' => 'b', 'body' => 'Oh Hai'));
        $this->assertEquals("---\na: b\n---\nOh Hai", $template);
    }

    public function testParsesUsingFrontMatter()
    {
        $input = 'user input';
        $output = array('user' => 'input');

        $parser = $this->getMockBuilder('Qissues\Application\Input\FrontMatterParser')->disableOriginalConstructor()->getMock();
        $parser
            ->expects($this->once())
            ->method('parse')
            ->with($input)
            ->will($this->returnValue($output))
        ;

        $format = new FrontMatterFormat($parser);
        $this->assertEquals($output, $format->parse($input));
    }

    public function testParsesWithCustomBodyField()
    {
        $input = 'user input';
        $output = array('user' => 'input');

        $parser = $this->getMockBuilder('Qissues\Application\Input\FrontMatterParser')->disableOriginalConstructor()->getMock();
        $parser
            ->expects($this->once())
            ->method('parse')
            ->with($input, 'body')
            ->will($this->returnValue($output))
        ;

        $format = new FrontMatterFormat($parser, 'body');
        $this->assertEquals($output, $format->parse($input));
    }
}
