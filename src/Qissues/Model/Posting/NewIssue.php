<?php

namespace Qissues\Model\Posting;

use Qissues\Model\Meta\User;
use Qissues\Model\Meta\Label;
use Qissues\Model\Meta\Type;
use Qissues\Model\Meta\Priority;
use Qissues\Model\Meta\Status;
use Qissues\System\DataType\ReadOnlyArrayAccess;

class NewIssue extends ReadOnlyArrayAccess
{
    protected $title;
    protected $description;
    protected $assignee;
    protected $priority;
    protected $type;
    protected $labels;
    protected $status;

    public function __construct($title, $description, User $assignee = null, Priority $priority = null, Type $type = null, $labels = null, Status $status = null)
    {
        $this->title = $title;
        $this->description = $description;
        $this->assignee = $assignee;
        $this->priority = $priority;
        $this->type = $type;
        $this->status = $status;

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

    public function getType()
    {
        return $this->type;
    }

    protected function addLabel(Label $label)
    {
        $this->labels[] = $label;
    }

    public function getLabels()
    {
        return $this->labels;
    }

    public function getStatus()
    {
        return $this->status;
    }
}
