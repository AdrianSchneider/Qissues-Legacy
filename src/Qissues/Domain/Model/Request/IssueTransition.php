<?php

namespace Qissues\Domain\Model\Request;

use Qissues\Domain\Model\Number;
use Qissues\Domain\Model\Message;
use Qissues\Domain\Shared\Status;

/**
 * Represents a request to change an issue's status
 */
class IssueTransition
{
    protected $issue;
    protected $status;
    protected $detailsBuilder;
    protected $comment;

    /**
     * @param Number $issue
     * @param Status $status
     * @param Callable $detailsBuilder
     * @param Message|null $comment
     */
    public function __construct(Number $issue, Status $status, $detailsBuilder, Message $comment = null)
    {
        $this->issue = $issue;
        $this->status = $status;
        $this->detailsBuilder = $detailsBuilder;
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

    public function getDetailsBuilder()
    {
        return $this->detailsBuilder;
    }

    public function getComment()
    {
        return $this->comment;
    }
}
