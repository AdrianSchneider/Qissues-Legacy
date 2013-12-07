<?php

namespace Qissues\Domain\Service;

use Qissues\Domain\Model\CriteriaFilter;
use Qissues\Domain\Model\CriteriaSorter;
use Qissues\Domain\Model\SearchCriteria;
use Qissues\Domain\Model\IssueRepository;
use Qissues\Domain\Model\Response\Issues;

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
     * @return Issues
     */
    public function __invoke(SearchCriteria $criteria)
    {
        $issues = new Issues($this->repository->query($criteria));
        $filter = new CriteriaFilter($criteria);
        $sorter = new CriteriaSorter($criteria);

        return $issues->filter($filter)->sort($sorter);
    }
}
