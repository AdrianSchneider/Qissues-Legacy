<?php

namespace Qissues\Domain\Model\Request;

use Qissues\Domain\Model\Number;
use Qissues\Domain\Shared\Status;

/**
 * Represents a request to change an issue's status
 */
class IssueTransition
{
    protected $issue;
    protected $status;
    protected $details;
    protected $comment;

    /**
     * @param Number $issue
     * @param Status $status
     * @param IssueTransitionDetails|null $details
     * @param Message|null $comment
     */
    public function __construct(Number $issue, Status $status, IssueTransitionDetails $details = null, Message $comment = null)
    {
        $this->issue = $issue;
        $this->status = $status;
        $this->details = $details;
        $this->comment = $comment;
    }

    public function getIssue()
    {
        return $this->issue;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getDetails()
    {
        return $this->details;
    }

    public function getComment()
    {
        return $this->comment;
    }
}
