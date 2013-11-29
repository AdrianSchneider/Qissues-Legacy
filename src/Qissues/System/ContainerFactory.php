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
    public function create(array $config)
    {
        $container = new ContainerBuilder();
        $locator = new FileLocator(__DIR__ . '/../../../config');

        $loader = new YamlFileLoader($container, $locator);
        $loader->load('services.yml');
        $loader->load('trackers.yml');

        $container->setParameter('defaults', $container->getParameterBag()->all());

        foreach ($this->flatten($config) as $key => $value) {
            $container->setParameter($key, $value);
        }

        $container->set('container', $container);

        try {
            $container->setParameter(
                'mapping_class', 
                $container->getDefinition(sprintf(
                    'tracker.%s.metadata',
                    $container->getParameter('tracker')
                ))->getClass()
            );
        } catch (\Exception $e) {
            $container->setParameter('mapping_class', 'Qissues\Domain\Tracker\Metadata\NullMetadata');
        }

        $container->compile();
        return $container;
    }

    /**
     * Flatten an array to use dot notation
     * @param array $array
     * @param string $prefix (internal)
     * @return array flattened
     */
    protected function flatten($array, $prefix = '')
    {
        $result = array();
        foreach ($array as $key => $value) {
            if (substr($key, -1) == 's') {
                $result[$key] = $value;
            } elseif (is_array($value)) {
                $result = $result + $this->flatten($value, $prefix . $key . '.');
            } else {
                $result[$prefix . $key] = $value;
            }
        }

        return $result;
    }
}
