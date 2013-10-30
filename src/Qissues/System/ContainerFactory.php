<?php

namespace Qissues\System;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ContainerFactory
{
    /**
     * Creates a new ContainerInterface from services.yml
     * @return ContainerInterface
     */
    public function create()
    {
        $container = new ContainerBuilder();
        $locator = new FileLocator(__DIR__ . '/../../../config');

        $loader = new YamlFileLoader($container, $locator);
        $loader->load('services.yml');

        $container->compile();
        return $container;
    }
}
