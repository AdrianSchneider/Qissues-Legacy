<?php

namespace Qissues\Console\Input\Strategy\Issue;

use Qissues\Model\Issue;
use Qissues\Model\Tracker\IssueTracker;
use Qissues\Console\Input\ExternalFileEditor;
use Qissues\Console\Input\FileFormats\FileFormat;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EditStrategy implements IssueStrategy
{

    public function __construct(ExternalFileEditor $editor, FileFormat $fileFormat)
    {
        $this->editor = $editor;
        $this->fileFormat = $fileFormat;
    }

    function init(InputInterface $input, OutputInterface $output, Application $application) { }

    /**
     * Creates a NewIssue by editing a formatted file in an external editor
     *
     * @param IssueTracker $tracker
     * @return NewIssue
     */
    public function createNew(IssueTracker $tracker)
    {
        $mapping = $tracker->getMapping();
        $content = $this->editor->getEdited($this->fileFormat->seed($mapping->getEditFields()));
        return $mapping->toNewIssue($this->fileFormat->parse($content));
    }

    /**
     * Creates a NewIssue with changes to be applied against Issue
     *
     * @param IssueTracker $tracker
     * @param Issue $existing
     * @return NewIssue
     */
    public function updateExisting(IssueTracker $tracker, Issue $existing)
    {
        $mapping = $tracker->getMapping();
        $content = $this->editor->getEdited($this->fileFormat->seed($mapping->getEditFields($existing)));
        return $mapping->toNewIssue($this->fileFormat->parse($content));
    }
}
