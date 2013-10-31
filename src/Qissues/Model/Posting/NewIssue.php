<?php

namespace Qissues\Model\Posting;

use Qissues\Model\Meta\User;
use Qissues\Model\Meta\Priority;
use Qissues\System\DataType\ReadOnlyArrayAccess;

class NewIssue extends ReadOnlyArrayAccess
{
    public function __construct($title, $description, User $assignee = null, Priority $priority = null, array $types = array())
    {
        $this->title = $title;
        $this->description = $description;
        $this->assignee = $assignee;
        $this->priority = $priority;
        $this->types = $types;
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
}
