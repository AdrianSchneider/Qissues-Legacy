<?php

namespace Qissues\Domain\Model;

use Qissues\Domain\Shared\Status;
use Qissues\Domain\Shared\User;
use Qissues\Domain\Shared\Type;
use Qissues\Domain\Shared\Priority;
use Qissues\Domain\Shared\Milestone;

class IssueMetadata
{
    protected $status;
    protected $dateCreated;
    protected $dateUpdated;
    protected $assignee;
    protected $labels;
    protected $priority;
    protected $type;
    protected $milestone;

    public function __construct(Status $status, \DateTime $dateCreated, \DateTime $dateUpdated)
    {
        $this->status = $status;
        $this->dateCreated = $dateCreated;
        $this->dateUpdated = $dateUpdated;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    public function getDateUpdated()
    {
        return $this->dateUpdated;
    }

    public function setAssignee(User $assignee)
    {
        $this->assignee = $assignee;
    }

    public function getAssignee()
    {
        return $this->assignee;
    }

    public function setLabels(array $labels)
    {
        $this->labels = $labels;
    }

    public function getLabels()
    {
        return $this->labels;
    }

    public function setPriority(Priority $priority)
    {
        $this->priority = $priority;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function setMilestone()
    {
        $this->milestone = $milestone;
    }

    public function getMilestone()
    {
        return $this->milestone;
    }

    public function setType(Type $type)
    {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getCommentCount()
    {
        return 5;
    }
}
