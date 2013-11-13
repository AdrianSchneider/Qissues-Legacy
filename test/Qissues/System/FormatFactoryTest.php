<?php

namespace Qissues\Tests\System;

use Qissues\System\FormatFactory;

class FormatFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function test()
    {
        $format = $this->getMock('Qissues\Console\Input\FileFormats\FileFormat');

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
