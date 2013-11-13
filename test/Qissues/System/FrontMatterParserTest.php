<?php

namespace Qissues\Tests\System;

use Qissues\System\FrontMatterParser;

class FrontMatterParserTest extends \PHPUnit_Framework_TestCase
{
    public function testThrowsExceptionIfMalformed()
    {
        $input = 'asdfasdfas';

        $yml = $this->getMockBuilder('Symfony\Component\Yaml\Parser')->disableOriginalConstructor()->getMock();

        $this->setExpectedException('InvalidArgumentException');

        $templatedInput = new FrontMatterParser($yml);
        $templatedInput->parse($input);
    }

    public function testThrowsExceptionIfParsingFails()
    {
        $input = '--- abc --- def';

        $yml = $this->getMockBuilder('Symfony\Component\Yaml\Parser')->disableOriginalConstructor()->getMock();
        $yml
            ->expects($this->once())
            ->method('parse')
            ->with('abc')
            ->will($this->throwException($e = new \Exception()))
        ;

        $this->setExpectedException('InvalidArgumentException');

        $templateInput = new FrontMatterParser($yml);
        $templateInput->parse($input);
    }

    public function testReturnsParsedContent()
    {
        $input = "---\na: b\n---\npizza";

        $yml = $this->getMockBuilder('Symfony\Component\Yaml\Parser')->disableOriginalConstructor()->getMock();
        $yml
            ->expects($this->once())
            ->method('parse')
            ->with('a: b')
            ->will($this->returnValue(array('a' => 'b')))
        ;

        $templateInput = new FrontMatterParser($yml);
        $out = $templateInput->parse($input);

        $this->assertEquals(array(
            'a' => 'b',
            'description' => 'pizza'
        ), $out);
    }

    public function testPutBodyAsCustomKey()
    {
        $input = "---\na: b\n---\npizza";

        $yml = $this->getMockBuilder('Symfony\Component\Yaml\Parser')->disableOriginalConstructor()->getMock();
        $yml
            ->expects($this->once())
            ->method('parse')
            ->with('a: b')
            ->will($this->returnValue(array('a' => 'b')))
        ;

        $templateInput = new FrontMatterParser($yml);
        $out = $templateInput->parse($input, 'food');

        $this->assertEquals(array(
            'a' => 'b',
            'food' => 'pizza'
        ), $out);
    }
}
