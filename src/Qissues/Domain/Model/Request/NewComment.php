<?php

namespace Qissues\Domain\Model\Request;

use Qissues\Domain\Model\Number;
use Qissues\Domain\Model\Message;

/**
 * Represents a new comment on an issue
 */
class NewComment
{
    protected $issue;
    protected $message;

    /**
     * @param Number $issue
     * @param Message $message
     */
    public function __construct(Number $issue, Message $message)
    {
        $this->issue = $issue;
        $this->message = $message;
    }

    public function getIssue()
    {
        return $this->issue;
    }

    public function getMessage()
    {
        return $this->message;
    }
}
