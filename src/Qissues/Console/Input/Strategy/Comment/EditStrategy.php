<?php

namespace Qissues\Console\Input\Strategy\Comment;

use Qissues\Model\Posting\NewComment;
use Qissues\Model\Tracker\IssueTracker;
use Qissues\Console\Input\ExternalFileEditor;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EditStrategy implements CommentStrategy
{
    public function __construct(ExternalFileEditor $editor)
    {
        $this->editor = $editor;
    }

    /**
     * {@inheritDoc}
     */
    function init(InputInterface $input, OutputInterface $output, Application $application) { }

    /**
     * {@inheritDoc}
     */
    public function createNew(IssueTracker $tracker)
    {
        if ($content = $this->editor->getEdited('')) {
            return new NewComment($content);
        }
    }
}
