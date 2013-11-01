<?php

namespace Qissues\Model\Posting;

use Qissues\Model\Meta\User;
use Qissues\Model\Meta\Label;
use Qissues\Model\Meta\Priority;
use Qissues\System\DataType\ReadOnlyArrayAccess;

class NewIssue extends ReadOnlyArrayAccess
{
    public function __construct($title, $description, User $assignee = null, Priority $priority = null, Type $type = null, $labels = null)
    {
        $this->title = $title;
        $this->description = $description;
        $this->assignee = $assignee;
        $this->priority = $priority;
        $this->type = $type;

        $this->labels = array();
        if ($labels) {
            foreach ($labels as $label) {
                $this->addLabel($label);
            }
        }
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getAssignee()
    {
        return $this->assignee;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function getTypes()
    {
        return $this->types;
    }

    protected function addLabel(Label $label)
    {
        $this->labels[] = $label;
    }

    public function getLabels()
    {
        return $this->labels;
    }
}
