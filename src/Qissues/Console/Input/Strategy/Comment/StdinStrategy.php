<?php

namespace Qissues\Console\Input\Strategy\Comment;

use Qissues\Model\Posting\NewComment;
use Qissues\Model\Tracker\IssueTracker;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StdinStrategy implements CommentStrategy
{
    /**
     * {@inheritDoc}
     */
    function init(InputInterface $input, OutputInterface $output, Application $application) { }

    /**
     * {@inheritDoc}
     */
    function createNew(IssueTracker $tracker)
    {
        if ($input = file_get_contents('php://stdin')) {
            return new NewComment($input);
        }
    }
}
