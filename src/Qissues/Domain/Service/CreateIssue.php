<?php

namespace Qissues\Domain\Service;

use Qissues\Domain\Model\NewIssue;
use Qissues\Domain\Model\IssueRepository;

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
