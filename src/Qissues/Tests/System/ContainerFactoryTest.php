<?php

namespace Qissues\Tests\System;

use Qissues\System\ContainerFactory;

class ContainerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreatesAContainer()
    {
        $factory = new ContainerFactory();
        $container = $factory->create(array());
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\ContainerInterface', $container);
    }

    public function testSetsParametersFromConfig()
    {
        $factory = new ContainerFactory();
        $container = $factory->create(array(
            'a' => 'test',
            'b' => array(
                'c' => 'd'
            )
        ));
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\ContainerInterface', $container);
        $this->assertEquals('test', $container->getParameter('a'));
        $this->assertEquals('d', $container->getParameter('b.c'));
    }

    public function testContainerIsLocked()
    {
        $factory = new ContainerFactory();
        $container = $factory->create(array());
        $this->assertTrue($container->isFrozen());
    }

    public function testLoadedServices()
    {
        $factory = new ContainerFactory();
        $container = $factory->create(array());

        $this->assertInstanceOf('Symfony\Component\Yaml\Parser', $container->get('yaml_parser'));
    }

    public function testReportsArePreservedAsArrays()
    {
        $factory = new ContainerFactory();
        $container = $factory->create(array(
            'reports' => array(
                'a' => array('assignees' => 1),
                'b' => array('something' => 0)
            )
        ));
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\ContainerInterface', $container);
        $this->assertInternalType('array', $container->getParameter('reports.a'));
    }
}
