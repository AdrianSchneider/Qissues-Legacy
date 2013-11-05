<?php

namespace Qissues\System;

use Qissues\Console\Input\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Initializer
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

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

    public function initialize($tracker)
    {
        if (file_exists('.qissues')) {
            throw new Exception('.qissues already exists');
        }

        $parameters = array('tracker' => $tracker);
        foreach ($this->container->getParameter('defaults') as $key => $value) {
            if (strpos($key, $tracker) === 0) {
                $parameters[$key] = $value;
            }
        }

        file_put_contents('.qissues', $this->buildYml($parameters));
    }

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
