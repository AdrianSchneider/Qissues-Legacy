<?php

namespace Qissues\Model\Workflow;

use Qissues\Model\Issue;
use Qissues\Model\Meta\Status;

class Transition
{
    protected $issue;
    protected $status;
    protected $fields;

    public function __construct(Issue $issue, Status $status)
    {
        $this->issue = $issue;
        $this->status = $status;
    }

    public function getIssue()
    {
        return $this->issue;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function addFields(array $fields)
    {
        if ($this->fields) {
            throw new \BadMethodCallException('Cannot add fields more than once');
        }

        $this->fields = $fields;
    }

    public function getFields()
    {
        return $this->fields;
    }
}