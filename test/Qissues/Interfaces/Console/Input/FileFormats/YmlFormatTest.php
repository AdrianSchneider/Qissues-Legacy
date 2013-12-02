<?php

namespace Qissues\Interfaces\Console\Input\FileFormats;

use Qissues\Domain\Shared\Details;
use Qissues\Domain\Shared\ExpectedDetail;
use Qissues\Domain\Shared\ExpectedDetails;
use Qissues\Interfaces\Console\Input\FileFormats\YmlFormat;

class YmlFormatTest extends \PHPUnit_Framework_TestCase
{
    public function testSeedBasicFields()
    {
        $in = new ExpectedDetails(array(new ExpectedDetail('input', 'default')));
        $pairs = array('input' => 'default');
        $out = "input: default";

        $format = new YmlFormat(
            $this->getMockBuilder('Symfony\Component\Yaml\Parser')->disableOriginalConstructor()->getMock(),
            $dumper = $this->getMockBuilder('Symfony\Component\Yaml\Dumper')->disableOriginalConstructor()->getMock(),
            $depth = 500
        );

        $dumper
            ->expects($this->once())
            ->method('dump', $depth)
            ->with($pairs)
            ->will($this->returnValue($out))
        ;

        $this->assertEquals($out, $format->seed($in));
    }

    public function testSeedIncludesOptionsAsComments()
    {
        $in = new ExpectedDetails(array(new ExpectedDetail('priority', 3, array(1, 2, 3, 4, 5))));
        $pairs = array('priority' => 3);
        $out = "priority: 3";

        $format = new YmlFormat(
            $this->getMockBuilder('Symfony\Component\Yaml\Parser')->disableOriginalConstructor()->getMock(),
            $dumper = $this->getMockBuilder('Symfony\Component\Yaml\Dumper')->disableOriginalConstructor()->getMock(),
            $depth = 500
        );

        $dumper
            ->expects($this->once())
            ->method('dump', $depth)
            ->with($pairs)
            ->will($this->returnValue($out))
        ;

        $this->assertEquals("# [1, 2, 3, 4, 5]\n$out", $format->seed($in));
    }

    public function testSeedUnquotesEmptyStrings()
    {
        $in = new ExpectedDetails(array(new ExpectedDetail('input')));
        $pairs = array('input' => '');
        $yml = "input: ''";
        $out = "input: ";

        $format = new YmlFormat(
            $this->getMockBuilder('Symfony\Component\Yaml\Parser')->disableOriginalConstructor()->getMock(),
            $dumper = $this->getMockBuilder('Symfony\Component\Yaml\Dumper')->disableOriginalConstructor()->getMock(),
            $depth = 500
        );

        $dumper
            ->expects($this->once())
            ->method('dump', $depth)
            ->with($pairs)
            ->will($this->returnValue($yml))
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

        $this->assertEquals(new Details($out), $format->parse($in));
    }
}
