<?php

namespace Qissues\Console\Input;

use Qissues\Model\Issue;
use Qissues\Model\Tracker\IssueTracker;

class ExternalIssueFactory
{
    public function __construct(ExternalFileEditor $editor, FileFormat $fileFormat)
    {
        $this->editor = $editor;
        $this->fileFormat = $fileFormat;
    }

    /**
     * Creates a NewIssue by editing a formatted file in an external editor
     *
     * @param IssueTracker $tracker
     * @return NewIssue
     */
    public function createForTracker(IssueTracker $tracker)
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
    public function updateForTracker(IssueTracker $tracker, Issue $existing)
    {
        $mapping = $tracker->getMapping();
        $content = $this->editor->getEdited($this->fileFormat->seed($mapping->getEditFields($existing)));
        return $mapping->toNewIssue($this->fileFormat->parse($content));
    }
}
