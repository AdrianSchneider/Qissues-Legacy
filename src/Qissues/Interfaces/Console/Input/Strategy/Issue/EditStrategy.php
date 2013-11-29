<?php

namespace Qissues\Interfaces\Console\Input\Strategy\Issue;

use Qissues\Domain\Model\Issue;
use Qissues\Trackers\Shared\IssueTracker;
use Qissues\Interfaces\Console\Input\ExternalFileEditor;
use Qissues\Interfaces\Console\Input\FileFormats\FileFormat;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EditStrategy implements IssueStrategy
{
    protected $editor;
    protected $fileFormat;

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
        $content = trim($this->editor->getEdited($this->fileFormat->seed($mapping->getEditFields())));

        if (!$content) {
            return;
        }

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

        if (!$content) {
            return;
        }

        return $mapping->toNewIssue($this->fileFormat->parse($content));
    }
}
