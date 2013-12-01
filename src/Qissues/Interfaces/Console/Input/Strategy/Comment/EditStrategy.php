<?php

namespace Qissues\Interfaces\Console\Input\Strategy\Comment;

use Qissues\Domain\Model\Message;
use Qissues\Application\Tracker\IssueTracker;
use Qissues\Interfaces\Console\Input\ExternalFileEditor;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EditStrategy implements CommentStrategy
{
    protected $editor;

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
            return new Message($content);
        }
    }
}
