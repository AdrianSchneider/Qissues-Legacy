<?php

namespace Qissues\Application\Input;

use Symfony\Component\DependencyInjection\ContainerInterface;

class FormatFactory
{
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Returns a FileFormat
     *
     * @param string|null $type
     * @return FileFormat
     */
    public function getFormat($specific = null)
    {
        return $this->container->get(sprintf(
            'console.input.format.%s',
            $specific
                ? $this->container->getParameter("console.input.default_{$specific}_format")
                : $this->container->getParameter('console.input.default_format')
        ));
    }
}
