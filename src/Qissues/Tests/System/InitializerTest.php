<?php

namespace Qissues\Tests\System;

use Qissues\System\Initializer;

class InitializerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSupportedTrackersFromContainer()
    {
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')->disableOriginalConstructor()->getMock();
        $container
            ->expects($this->once())
            ->method('getServiceIds')
            ->will($this->returnValue(array(
                'tracker.a',
                'tracker.b'
            )))
        ;

        $filesystem = $this->getMockBuilder('Symfony\Component\Filesystem\Filesystem')->disableOriginalConstructor()->getMock();

        $initializer = new Initializer($container, $filesystem);
        $trackers = $initializer->getSupportedTrackers();

        $this->assertEquals(array('a', 'b'), $trackers);
    }

    public function testInitializeThrowsExceptionWhenFileExists()
    {
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')->disableOriginalConstructor()->getMock();

        $filesystem = $this->getMockBuilder('Symfony\Component\Filesystem\Filesystem')->disableOriginalConstructor()->getMock();
        $filesystem
            ->expects($this->once())
            ->method('exists')
            ->with('.qissues')
            ->will($this->returnValue(true))
        ;

        $this->setExpectedException('Exception', 'already');

        $initializer = new Initializer($container, $filesystem);
        $initializer->initialize('doesnt matter');
    }

    public function testInitializerCreatesQissuesFileWithConfigPreFilled()
    {
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')->disableOriginalConstructor()->getMock();
        $container
            ->expects($this->once())
            ->method('getParameter')
            ->with('defaults')
            ->will($this->returnValue(array(
                'tracka.a' => 'b'
            )))
        ;

        $filesystem = $this->getMockBuilder('Symfony\Component\Filesystem\Filesystem')->disableOriginalConstructor()->getMock();
        $filesystem
            ->expects($this->once())
            ->method('exists')
            ->with('.qissues')
            ->will($this->returnValue(false))
        ;
        $filesystem
            ->expects($this->once())
            ->method('dumpFile')
            ->with('.qissues', "tracker: tracka\n#tracka.a: b")
        ;

        $initializer = new Initializer($container, $filesystem);
        $initializer->initialize('tracka');
    }
}
