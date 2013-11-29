<?php

namespace Qissues\Interfaces\Console\Input\Strategy\Comment;

use Qissues\Domain\Model\NewComment;
use Qissues\Trackers\Shared\IssueTracker;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OptionStrategy implements CommentStrategy
{
    protected $input;

    /**
     * {@inheritDoc}
     */
    function init(InputInterface $input, OutputInterface $output, Application $application)
    {
        $this->input = $input;
    }

    /**
     * {@inheritDoc}
     */
    function createNew(IssueTracker $tracker)
    {
        if ($message = $this->input->getOption('message')) {
            return new NewComment($message);
        }
    }
}
