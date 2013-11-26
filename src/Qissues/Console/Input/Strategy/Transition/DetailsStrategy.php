<?php

namespace Qissues\Console\Input\Strategy\Transition;

use Qissues\Model\Workflow\TransitionDetails;
use Qissues\Model\Workflow\TransitionRequirements;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface DetailsStrategy
{
    /**
     * Optionally require some more environment information
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param Application $console application
     */
    function init(InputInterface $input, OutputInterface $output, Application $application);

    /**
     * Prepares details for a transition
     *
     * @param TransitionRequirements $requirements
     * @return TransitionDetails
     */
    function create(TransitionRequirements $requirements);
}
