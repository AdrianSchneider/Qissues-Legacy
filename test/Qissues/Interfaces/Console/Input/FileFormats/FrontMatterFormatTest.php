<?php

namespace Qissues\Tests\Console\Input\FileFormats;

use Qissues\Domain\Shared\Details;
use Qissues\Domain\Shared\ExpectedDetail;
use Qissues\Domain\Shared\ExpectedDetails;
use Qissues\Interfaces\Console\Input\FileFormats\FrontMatterFormat;

class FrontMatterFormatTest extends \PHPUnit_Framework_TestCase
{
    public function testBasicSeed()
    {
        $parser = $this->getMockBuilder('Qissues\Application\Input\FrontMatterParser')->disableOriginalConstructor()->getMock();
        $dumper = $this->getMockBuilder('Symfony\Component\Yaml\Dumper')->disableOriginalConstructor()->getMock();
        $dumper
            ->expects($this->once())
            ->method('dump')
            ->with(array('a' => 1, 'b' => 2))
            ->will($this->returnValue("a: 1\nb: 2"))
        ;

        $format = new FrontMatterFormat($parser, $dumper);

        $expectations = new ExpectedDetails(array(
            new ExpectedDetail('description'),
            new ExpectedDetail('a', 1),
            new ExpectedDetail('b', 2)
        ));

        $this->assertEquals("---\na: 1\nb: 2\n---\n", $format->seed($expectations));
    }

    public function testSeedWithOptionsComment()
    {
        $parser = $this->getMockBuilder('Qissues\Application\Input\FrontMatterParser')->disableOriginalConstructor()->getMock();
        $dumper = $this->getMockBuilder('Symfony\Component\Yaml\Dumper')->disableOriginalConstructor()->getMock();
        $dumper
            ->expects($this->once())
            ->method('dump')
            ->with(array('input' => 'default'))
            ->will($this->returnValue("input: 'default'"))
        ;

        $format = new FrontMatterFormat($parser, $dumper);

        $expectations = new ExpectedDetails(array(
            new ExpectedDetail('description'),
            new ExpectedDetail('input', 'default', array('a', 'b', 'c'))
        ));

        $this->assertEquals("---\ninput: 'default' # [a, b, c]\n---\n", $format->seed($expectations));
    }

    public function testSeedWithMultipleOptionFieldsWorks()
    {
        $parser = $this->getMockBuilder('Qissues\Application\Input\FrontMatterParser')->disableOriginalConstructor()->getMock();
        $dumper = $this->getMockBuilder('Symfony\Component\Yaml\Dumper')->disableOriginalConstructor()->getMock();
        $dumper
            ->expects($this->once())
            ->method('dump')
            ->with(array('input' => 'default', 'priority' => 3))
            ->will($this->returnValue("input: 'default'\npriority: 3"))
        ;

        $format = new FrontMatterFormat($parser, $dumper);

        $expectations = new ExpectedDetails(array(
            new ExpectedDetail('description'),
            new ExpectedDetail('input', 'default', array('a', 'b', 'c')),
            new ExpectedDetail('priority', 3, range(1, 5))
        ));

        $yml = $format->seed($expectations);

        $this->assertEquals("---\ninput: 'default' # [a, b, c]\npriority: 3 # [1, 2, 3, 4, 5]\n---\n", $yml);
    }

    public function testSeedStripsQuotingFromEmptyStrings()
    {
        $parser = $this->getMockBuilder('Qissues\Application\Input\FrontMatterParser')->disableOriginalConstructor()->getMock();
        $dumper = $this->getMockBuilder('Symfony\Component\Yaml\Dumper')->disableOriginalConstructor()->getMock();
        $dumper
            ->expects($this->once())
            ->method('dump')
            ->with(array('input' => ''))
            ->will($this->returnValue("input: ''"))
        ;

        $format = new FrontMatterFormat($parser, $dumper);

        $expectations = new ExpectedDetails(array(
            new ExpectedDetail('description'),
            new ExpectedDetail('input')
        ));

        $this->assertEquals("---\ninput: \n---\n", $format->seed($expectations));
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
        $dumper = $this->getMockBuilder('Symfony\Component\Yaml\Dumper')->disableOriginalConstructor()->getMock();

        $format = new FrontMatterFormat($parser, $dumper);
        $this->assertEquals($output, $format->parse($input)->getDetails());
    }
}
