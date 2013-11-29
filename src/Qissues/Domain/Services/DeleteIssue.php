<?php

namespace Qissues\Model\Services;

use Qissues\Model\Querying\Number;
use Qissues\Model\Tracker\IssueRepository;

class DeleteIssue
{
    protected $repository;

    public function __construct(IssueRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Deletes an issue
     *
     * @param Number $issue
     */
    public function __invoke(Number $issue)
    {
        $this->repository->delete($issue);
    }
}
