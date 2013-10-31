<?php

namespace Qissues\Console\Input;

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
}
