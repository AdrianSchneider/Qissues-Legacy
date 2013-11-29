<?php

namespace Qissues\Domain\Model\Request;

use Qissues\Domain\Model\Number;
use Qissues\Domain\Model\Message;
use Qissues\Domain\Shared\User;

/**
 * Represents a request to assign an issue
 */
class IssueAssignment
{
    protected $issue;
    protected $assignee;
    protected $comment;

    /**
     * @param Number $issue
     * @param User $assignee
     * @param Message|null $comment
     */
    public function __construct(Number $issue, User $assignee, Message $comment = null)
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
