<?php

namespace Qissues\Tests\System;

use Qissues\System\ContainerFactory;

class ContainerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreatesAContainer()
    {
        $factory = new ContainerFactory();
        $container = $factory->create();
        $this->assertInstanceOf('Symfony\Component\DependencyInjection\ContainerInterface', $container);
    }

    public function testContainerIsLocked()
    {
        $factory = new ContainerFactory();
        $container = $factory->create();
        $this->assertTrue($container->isFrozen());
    }

    public function testLoadedServices()
    {
        $factory = new ContainerFactory();
        $container = $factory->create();

        $this->assertInstanceOf('Symfony\Component\Yaml\Parser', $container->get('yaml_parser'));
    }
}
