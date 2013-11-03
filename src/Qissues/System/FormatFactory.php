<?php

namespace Qissues\System;

use Symfony\Component\DependencyInjection\ContainerInterface;

class FormatFactory
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFormat()
    {
        return $this->container->get(sprintf(
            'console.input.format.%s',
            $this->container->getParameter('console.input.default_format')
        ));
    }
}
