<?php

namespace Qissues\System;

use Qissues\Console\Input\Exception;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Initializer
{
    protected $container;
    protected $filesystem;

    public function __construct(ContainerInterface $container, Filesystem $filesystem)
    {
        $this->container = $container;
        $this->filesystem = $filesystem;
    }

    /**
     * Returns a list of supported trackers to initialize
     * @return array trackers
     */
    public function getSupportedTrackers()
    {
        $trackers = array();
        foreach ($this->container->getServiceIds() as $id) {
            $matches = null;
            if (preg_match('/^tracker\.([a-z]+)$/', $id, $matches)) {
                $trackers[] = $matches[1];
            }
        }

        return $trackers;
    }

    /**
     * Creates a .qissues file
     * @param string $tracker
     */
    public function initialize($tracker)
    {
        if ($this->filesystem->exists('.qissues')) {
            throw new Exception('.qissues already exists');
        }

        $parameters = array('tracker' => $tracker);
        foreach ($this->container->getParameter('defaults') as $key => $value) {
            if (strpos($key, $tracker) === 0) {
                $parameters[$key] = $value;
            }
        }

        $this->filesystem->dumpFile('.qissues', $this->buildYml($parameters));
    }

    /**
     * Generates the YML to write
     * @param array $parameters
     * @return string YML
     */
    protected function buildYml(array $parameters)
    {
        $out = array();
        $counter = 0;
        foreach ($parameters as $key => $value) {
            if ($counter++) {
                $key = "#$key";
            }
            $out[] = "$key: $value";
        }

        return implode("\n", $out);
    }
}
