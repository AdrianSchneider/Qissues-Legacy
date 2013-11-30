<?php

namespace Qissues\Domain\Model\Request;

use Qissues\Domain\Model\Number;
use Qissues\Domain\Model\Transition;
use Qissues\Domain\Model\Message;

/**
 * Represents a request to change an issue's status
 */
class IssueTransition
{
    protected $issue;
    protected $transition;
    protected $comment;

    /**
     * @param Number $issue
     * @param Transition $transition
     * @param Message|null $comment
     */
    public function __construct(Number $issue, Transition $transition, Message $comment = null)
    {
        $this->issue = $issue;
        $this->transition = $transition;
        $this->comment = $comment;
    }

    public function getIssue()
    {
        return $this->issue;
    }

    public function getTransition()
    {
        return $this->transition;
    }

    public function getComment()
    {
        return $this->comment;
    }
}
