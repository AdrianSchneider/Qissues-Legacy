<?php

namespace Qissues\Domain\Model\Request;

use Qissues\Domain\Model\Number;
use Qissues\Domain\Shared\Milestone;

/**
 * Represents a request to plan an issue
 */
class IssuePlan
{
    protected $issue;
    protected $milestone;

    /**
     * @param Number $issue
     * @param Milestone $milestone
     */
    public function __construct(Number $issue, Milestone $milestone)
    {
        $this->issue = $issue;
        $this->milestone = $milestone;
    }

    public function getIssue()
    {
        return $this->issue;
    }

    public function getMilestone()
    {
        return $this->milestone;
    }
}
