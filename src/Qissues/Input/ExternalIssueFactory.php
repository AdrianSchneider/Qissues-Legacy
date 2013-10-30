<?php

namespace Qissues\Input;

use Qissues\Format\IssueConverter;

class ExternalIssueFactory
{
    public function __construct(ExternalFileEditor $editor, IssueConverter $converter, TemplatedInput $templated)
    {
        $this->editor = $editor;
        $this->converter = $converter;
        $this->templatedInput = $templated;
    }

    /**
     * Creates a NewIssue from an external program's input
     * @param IssueTracker $tracker
     * @return NewIssue
     */
    public function createForTracker(IssueTracker $tracker)
    {
        return $this->converter->toNewIssue(
            $this->editor->getEdited($this->getTemplate($tracker))
        );
    }

    public function updateForTracker(IssueTracker $tracker, Issue $existing)
    {
        return $this->converter->toNewIssue(
            $this->editor->getEdited($this->getTemplate($tracker, $existing))
        );
    }

    /**
     * Defines the template to seed the file with
     * @param IssueTracker $tracker
     * @return string initial file contents
     */
    protected function getTemplate(IssueTracker $tracker)
    {
        $template = '';
        foreach ($tracker->getEditorFields() as $key => $value) {
            $template .= "$key: $value\n";
        }

        $template .= "---\nissue description...";
    }

    /**
     * Fetch input from user input
     * @param Connector $connector
     * @return array issue details
     */
    protected function getIssueDetailsFromExternal(Connector $connector)
    {
        $template = '';
        foreach ($connector->getEditorFields() as $key => $value) {
            $template .= "$key: $value\n";
        }
        $template .= "---\nDescription";

        return $this->templatedInput->parse($this->getFromEditor($template));
    }
}
