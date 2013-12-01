<?php

namespace Qissues\Tests\System;

use Qissues\Application\Container\ContainerFactory;

class ContainerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreatesAContainer()
    {
        $factory = new ContainerFactory(__DIR__ . '/../../../../config');
        $container = $factory->create(array());
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\ContainerInterface', $container);
    }

    public function testSetsParametersFromConfig()
    {
        $factory = new ContainerFactory(__DIR__ . '/../../../../config');
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
        $factory = new ContainerFactory(__DIR__ . '/../../../../config');
        $container = $factory->create(array());
        $this->assertTrue($container->isFrozen());
    }

    public function testLoadedServices()
    {
        $factory = new ContainerFactory(__DIR__ . '/../../../../config');
        $container = $factory->create(array());

        $this->assertInstanceOf('Symfony\Component\Yaml\Parser', $container->get('yaml_parser'));
    }

    public function testPluralKeysArePreservedAsArrays()
    {
        $factory = new ContainerFactory(__DIR__ . '/../../../../config');
        $container = $factory->create(array(
            'reports' => array(
                'a' => array('assignees' => 1),
                'b' => array('something' => 0)
            ),
            'something.something.peanuts' => array(
                'a' => true,
                'b' => true
            )
        ));

        $this->assertEquals($container->getParameter('reports'), array(
            'a' => array('assignees' => 1),
            'b' => array('something' => 0)
        ));
        $this->assertEquals($container->getParameter('something.something.peanuts'), array(
            'a' => true,
            'b' => true
        ));
    }

    public function testSetsContainerAsItself()
    {
        $factory = new ContainerFactory(__DIR__ . '/../../../../config');
        $container = $factory->create(array());

        $this->assertSame($container, $container->get('container'));
    }
}
