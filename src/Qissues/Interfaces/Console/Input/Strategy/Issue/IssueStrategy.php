<?php

namespace Qissues\Interfaces\Console\Input\Strategy\Issue;

use Qissues\Domain\Model\Issue;
use Qissues\Application\Tracker\IssueTracker;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface IssueStrategy
{
    /**
     * Optionally require some more environment information
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param Application $console application
     */
    function init(InputInterface $input, OutputInterface $output, Application $application);

    /**
     * Creates a new NewIssue instance
     * @param IssueTracker $tracker
     * @return NewIssue|Message
     */
    function createNew(IssueTracker $tracker);

    /**
     * Creates a new NewIssue instance for changes
     * @param IssueTracker Tracker
     * @param Issue $issue
     * @return NewIssue|Message
     */
    function updateExisting(IssueTracker $tracker, Issue $existing);
}
