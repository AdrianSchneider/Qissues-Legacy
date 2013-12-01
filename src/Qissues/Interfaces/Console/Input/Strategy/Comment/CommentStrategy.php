<?php

namespace Qissues\Interfaces\Console\Input\Strategy\Comment;

use Qissues\Application\Tracker\IssueTracker;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface CommentStrategy
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
     * Creates a Message instance
     *
     * @param IssueTracker $tracker
     * @return Message|null
     */
    function createNew(IssueTracker $tracker);
}
