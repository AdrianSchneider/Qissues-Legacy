<?php

namespace Qissues\Application\Container;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ContainerFactory
{
    protected $dir;

    public function __construct($configDir)
    {
        $this->dir = $configDir;
    }

   /**
     * Creates a new ContainerInterface from services.yml
     * @return ContainerInterface
     */
    public function create(array $config)
    {
        $container = new ContainerBuilder();
        $locator = new FileLocator($this->dir);

        $loader = new YamlFileLoader($container, $locator);
        foreach (glob($this->dir . '/*.yml') as $file) {
            $loader->load(basename($file));
        }

        $container->setParameter('defaults', $container->getParameterBag()->all());

        foreach ($this->flatten($config) as $key => $value) {
            $container->setParameter($key, $value);
        }

        $container->set('container', $container);

        try {
            $container->setParameter(
                'tracker.mapping_class', 
                $container->getDefinition(sprintf(
                    'tracker.%s.metadata',
                    $container->getParameter('tracker')
                ))->getClass()
            );
        } catch (\Exception $e) {
            $container->setParameter('mapping_class', 'Qissues\Application\Tracker\Metadata\NullMetadata');
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
