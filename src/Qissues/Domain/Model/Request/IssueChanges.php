<?php

namespace Qissues\Domain\Model\Request;

use Qissues\Domain\Model\Number;

/**
 * Represents a request to update an issue
 */
class IssueChanges
{
    protected $issue;
    protected $changes;

    /**
     * @param Number $issue
     * @param NewIssue $changes
     */
    public function __construct(Number $issue, NewIssue $changes)
    {
        $this->issue = $issue;
        $this->changes = $changes;
    }

    public function getIssue()
    {
        return $this->issue;
    }

    public function getChanges()
    {
        return $this->changes;
    }
}
