<?php

namespace Qissues\Console\Input;

use Qissues\Model\Tracker\IssueTracker;
use Qissues\Model\Tracker\FieldMapping;

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
        $mapping = $tracker->getMapping();
        $content = $this->editor->getEdited($this->getTemplate($mapping));

        return $mapping->toNewIssue($this->parser->parse($content));
    }

    /**
     * Defines the template to seed the file with
     * TODO decouple from frontmatter parser
     *
     * @param FieldMapping $mapping
     * @param Issue|null $issue
     * @return string initial file contents
     */
    protected function getTemplate(FieldMapping $mapping, Issue $issue = null)
    {
        $template = "---\n";
        foreach ($mapping->getEditFields() as $key => $value) {
            $template .= "$key: $value\n";
        }

        $template .= "---\nissue description...";
        return $template;
    }
}
