<?php

namespace Qissues\Model\Core;

use Qissues\Model\Core\Meta\Status;
use Qissues\Model\Core\Meta\Priority;
use Qissues\Model\Core\Meta\Type;
use Qissues\Model\Core\Meta\User;
use Qissues\Model\Core\Meta\Label;
use Qissues\System\DataType\ReadOnlyArrayAccess;

class Issue extends ReadOnlyArrayAccess
{
    protected $id;
    protected $title;
    protected $description;
    protected $status;
    protected $dateCreated;
    protected $dateupdated;
    protected $assignee;
    protected $priority;
    protected $type;
    protected $labels;
    protected $comments;

    public function __construct($id, $title, $description, Status $status, \DateTime $dateCreated, \DateTime $dateUpdated, User $assignee = null, Priority $priority = null, Type $type = null, $labels = null, $comments = 0)
    {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;

        $this->status = $status;
        $this->dateCreated = $dateCreated;
        $this->dateUpdated = $dateUpdated;

        $this->assignee = $assignee;
        $this->priority = $priority;
        $this->type = $type;

        if ($labels) {
            $this->labels = array();
            array_walk($labels, array($this, 'addLabel'));
        } else {
            $this->labels = array();
        }

        $this->comments = $comments;
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
        return $this->type;
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
        return $this->comments;
    }

    protected function addLabel(Label $label)
    {
        $this->labels[] = $label;
    }

    public function getLabels()
    {
        return $this->labels;
    }

    public function getComments()
    {
        return $this->comments;
    }
}
