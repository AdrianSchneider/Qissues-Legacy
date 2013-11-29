<?php

namespace Qissues\Interfaces\Console\Input\Strategy\Comment;

use Qissues\Domain\Model\Message;
use Qissues\Trackers\Shared\IssueTracker;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InteractiveStrategy implements CommentStrategy
{
    protected $input;
    protected $output;
    protected $dialog;

    /**
     * Optionally require some more environment information
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param Application $application
     */
    function init(InputInterface $input, OutputInterface $output, Application $application)
    {
        if ($this->input) {
            throw new \BadMethodCallException('Can only init once');
        }
        if (!$input->isInteractive()) {
            throw new \RunTimeException('Input is not interactive');
        }

        $this->input = $input;
        $this->output = $output;
        $this->dialog = $application->getHelperSet()->get('dialog');
    }

    /**
     * Creates a new NewIssue instance
     * @param IssueTracker $tracker
     * @return Message|null if empty
     */
    function createNew(IssueTracker $tracker)
    {
        if ($message = $this->dialog->ask($this->output, 'Comment: ', '')) {
            return new Message($message);
        }
    }
}
