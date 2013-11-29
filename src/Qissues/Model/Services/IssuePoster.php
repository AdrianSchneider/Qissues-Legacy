<?php

namespace Qissues\Model\Services;

use Qissues\Model\Querying\Number;
use Qissues\Model\Posting\NewIssue;
use Qissues\Model\Tracker\IssueRepository;

class IssuePoster
{
    /**
     * Persists a NewIssue to an issue tracker
     *
     * @param NewIssue $issue
     * @param IssueRepository $repository
     * @return Number
     */
    public function create(NewIssue $issue, IssueRepository $repository)
    {
        return $repository->persist($issue);
    }

    /**
     * Updates an existing issue
     *
     * @param NewIssue $changes
     * @param Number $issue
     * @param IssueRepository $repository
     */
    public function update(NewIssue $changes, Number $issue, IssueRepository $repository)
    {
        $repository->update($changes, $issue);
    }
}
