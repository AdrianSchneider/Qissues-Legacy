<?php

namespace Qissues\Domain\Model\Request;

use Qissues\Domain\Model\Number;
use Qissues\Domain\Model\Request\NewComment;
use Qissues\Domain\Shared\User;

class IssueAssignment
{
    protected $issue;
    protected $assignee;
    protected $comment;

    public function __construct(Number $issue, User $assignee, NewComment $comment = null)
    {
        $this->issue = $issue;
        $this->assignee = $assignee;
        $this->comment = $comment;
    }

    public function getIssue()
    {
        return $this->issue;
    }

    public function getAssignee()
    {
        return $this->assignee;
    }

    public function getComment()
    {
        return $this->comment;
    }
}
