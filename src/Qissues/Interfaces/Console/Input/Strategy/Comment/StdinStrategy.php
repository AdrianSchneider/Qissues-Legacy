<?php

namespace Qissues\Interfaces\Console\Input\Strategy\Comment;

use Qissues\Domain\Model\NewComment;
use Qissues\Application\Tracker\IssueTracker;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StdinStrategy implements CommentStrategy
{
    protected $inputStream;

    public function __construct($inputStream = 'php://stdin')
    {
        $this->inputStream = $inputStream;
    }

    /**
     * {@inheritDoc}
     */
    function init(InputInterface $input, OutputInterface $output, Application $application) { }

    /**
     * {@inheritDoc}
     */
    function createNew(IssueTracker $tracker)
    {
        if ($input = trim(file_get_contents($this->inputStream))) {
            return new NewComment($input);
        }
    }
}
