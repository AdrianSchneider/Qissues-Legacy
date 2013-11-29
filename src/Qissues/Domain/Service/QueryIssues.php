<?php

namespace Qissues\Domain\Service;

use Qissues\Domain\Model\SearchCriteria;
use Qissues\Domain\Model\IssueRepository;

class QueryIssues
{
    /**
     * @param IssueRepository $repository
     */
    public function __construct(IssueRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Query the issue repository
     *
     * @param SearchCriteria $criteria
     * @return Issue[]
     */
    public function __invoke(SearchCriteria $criteria)
    {
        return $this->repository->query($criteria);
    }
}
