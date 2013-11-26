<?php

namespace Qissues\Console\Input\FileFormats;

use Qissues\Console\Input\FileFormats\YmlFormat;

class YmlFormatTest extends \PHPUnit_Framework_TestCase
{
    public function testSeed()
    {
        $in = array('a' => 'b');
        $out = 'Y: ML';

        $format = new YmlFormat(
            $this->getMockBuilder('Symfony\Component\Yaml\Parser')->disableOriginalConstructor()->getMock(),
            $dumper = $this->getMockBuilder('Symfony\Component\Yaml\Dumper')->disableOriginalConstructor()->getMock()
        );

        $dumper
            ->expects($this->once())
            ->method('dump')
            ->with($in)
            ->will($this->returnValue($out))
        ;

        $this->assertEquals($out, $format->seed($in));
    }

    public function testParse()
    {
        $in = 'Y: ML';
        $out = array('a' => 'b');

        $format = new YmlFormat(
            $parser = $this->getMockBuilder('Symfony\Component\Yaml\Parser')->disableOriginalConstructor()->getMock(),
            $this->getMockBuilder('Symfony\Component\Yaml\Dumper')->disableOriginalConstructor()->getMock()
        );

        $parser
            ->expects($this->once())
            ->method('parse')
            ->with($in)
            ->will($this->returnValue($out))
        ;

        $this->assertEquals($out, $format->parse($in));
    }
}
