<?php

namespace Qissues\Console\Input\Strategy\Comment;

use Qissues\Model\Tracker\IssueTracker;
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
     * Creates a NewComment instance
     *
     * @param IssueTracker $tracker
     * @return NewComment|null
     */
    function createNew(IssueTracker $tracker);
}
