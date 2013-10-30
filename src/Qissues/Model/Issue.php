<?php

namespace Qissues\Model;

use Qissues\Model\Meta\Status;
use Qissues\Model\Meta\Priority;
use Qissues\Model\Meta\Type;
use Qissues\Model\Meta\User;
use Qissues\System\DataType\ReadOnlyArrayAccess;

class Issue extends ReadOnlyArrayAccess
{
    public function __construct($id, $title, $description, Status $status, \DateTime $dateCreated, \DateTime $dateUpdated, User $assignee = null, Priority $priority = null, $types = null)
    {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;

        $this->status = $status;
        $this->dateCreated = $dateCreated;
        $this->dateUpdated = $dateUpdated;

        $this->assignee = $assignee;
        $this->priority = $priority;
        $this->types = $types;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function getType()
    {
        return $this->types ? reset($this->types) : null;
    }

    public function getTypes()
    {
        return $this->types;
    }

    public function getAssignee()
    {
        return $this->assignee;
    }

    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    public function getDateUpdated()
    {
        return $this->dateUpdated;
    }

    public function getCommentCount()
    {
        return 0;
    }
}
