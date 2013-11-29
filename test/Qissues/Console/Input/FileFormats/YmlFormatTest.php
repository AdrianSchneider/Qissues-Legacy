<?php

namespace Qissues\Interfaces\Console\Input\FileFormats;

use Qissues\Domain\Meta\Field;
use Qissues\Interfaces\Console\Input\FileFormats\YmlFormat;

class YmlFormatTest extends \PHPUnit_Framework_TestCase
{
    public function testSeed()
    {
        $in = array('a' => 'b');
        $out = 'Y: ML';

        $format = new YmlFormat(
            $this->getMockBuilder('Symfony\Component\Yaml\Parser')->disableOriginalConstructor()->getMock(),
            $dumper = $this->getMockBuilder('Symfony\Component\Yaml\Dumper')->disableOriginalConstructor()->getMock(),
            $depth = 500
        );

        $dumper
            ->expects($this->once())
            ->method('dump', $depth)
            ->with($in)
            ->will($this->returnValue($out))
        ;

        $this->assertEquals($out, $format->seed($in));
    }

    public function testSeedIncludesHintsIfFieldObjects()
    {
        $in = array(
            new Field('a', "def", array(1, 2, 3)),
            new Field('b', 'asdf', array(2, 3, 4))
        );

        $out = "\na: def\nb: ";

        $format = new YmlFormat(
            $this->getMockBuilder('Symfony\Component\Yaml\Parser')->disableOriginalConstructor()->getMock(),
            $dumper = $this->getMockBuilder('Symfony\Component\Yaml\Dumper')->disableOriginalConstructor()->getMock(),
            $depth = 500
        );

        $dumper
            ->expects($this->once())
            ->method('dump', $depth)
            ->with(array(
                'a' => 'def',
                'b' => 'asdf'
            ))
            ->will($this->returnValue($out))
        ;

        $this->assertEquals("# a: [1, 2, 3]\n# b: [2, 3, 4]\n$out", $format->seed($in));
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
