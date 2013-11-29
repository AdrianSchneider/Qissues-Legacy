<?php

namespace Qissues\Tests\System;

use Qissues\System\FormatFactory;

class FormatFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testSpecific()
    {
        $format = $this->getMock('Qissues\Interfaces\Console\Input\FileFormats\FileFormat');

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container
            ->expects($this->once())
            ->method('getParameter')
            ->with("console.input.default_specific_format")
            ->will($this->returnValue('specific_thing'))
        ;
        $container
            ->expects($this->once())
            ->method('get')
            ->with('console.input.format.specific_thing')
            ->will($this->returnValue($format))
        ;

        $factory = new FormatFactory($container);
        $this->assertSame($format, $factory->getFormat('specific'));
    }

    public function testFallback()
    {
        $format = $this->getMock('Qissues\Interfaces\Console\Input\FileFormats\FileFormat');

        $container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $container
            ->expects($this->once())
            ->method('getParameter')
            ->with($this->isType('string'))
            ->will($this->returnValue('mega'))
        ;
        $container
            ->expects($this->once())
            ->method('get')
            ->with('console.input.format.mega')
            ->will($this->returnValue($format))
        ;

        $factory = new FormatFactory($container);
        $this->assertSame($format, $factory->getFormat());
    }
}
