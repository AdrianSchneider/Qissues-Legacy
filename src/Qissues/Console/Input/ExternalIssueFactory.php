<?php

namespace Qissues\Console\Input;

use Qissues\Model\Tracker\IssueTracker;

class ExternalIssueFactory
{
    public function __construct(ExternalFileEditor $editor, FrontMatterParser $parser)
    {
        $this->editor = $editor;
        $this->parser = $parser;
    }

    /**
     * Creates a NewIssue from an external program's input
     * @param IssueTracker $tracker
     * @return NewIssue
     */
    public function createForTracker(IssueTracker $tracker)
    {
        $converter = $tracker->getIssueConverter();
        return $converter->toNewIssue(
            $this->templatedInput->parse(
                $this->editor->getEdited($this->getTemplate($converter))
            )
        );
    }

    /**
     * Creates a NewIssue from an external program's input
     * meant to be merged into an existing Issue
     *
     * @param IssueTracker $tracker
     * @param Issue $existing
     * @return NewIssue
     */
    public function updateForTracker(IssueTracker $tracker, Issue $existing)
    {
        $converter = $tracker->getIssueConverter();
        return $converter->toNewIssue(
            $this->editor->getEdited($this->getTemplate($converter, $existing))
        );
    }

    /**
     * Defines the template to seed the file with
     * @param IssueConverter $converter
     * @param Issue|null $issue
     * @return string initial file contents
     */
    protected function getTemplate(IssueConverter $converter, Issue $issue = null)
    {
        $template = '';
        foreach ($converter->getFields() as $key => $value) {
            $template .= "$key: $value\n";
        }

        $template .= "---\nissue description...";

        return $template;
    }
}
