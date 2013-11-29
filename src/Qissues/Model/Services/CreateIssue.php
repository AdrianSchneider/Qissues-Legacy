<?php

namespace Qissues\Model\Services;

use Qissues\Model\Posting\NewIssue;
use Qissues\Model\Tracker\IssueRepository;

class CreateIssue
{
    protected $repository;

    public function __construct(IssueRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Persists a NewIssue to an issue tracker
     *
     * @param NewIssue $issue
     * @return Number
     */
    public function __invoke(NewIssue $issue)
    {
        return $this->repository->persist($issue);
    }
}
