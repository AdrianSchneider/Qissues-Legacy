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
     * The repository is responsible for as much filtering as possible,
     * finally re-filtering and sorting at run-time afterward.
     *
     * @param SearchCriteria $criteria
     * @return Issues
     */
    public function __invoke(SearchCriteria $criteria)
    {
        $issues = new Issues($this->repository->query($criteria));

        $filter = new CriteriaFilter($criteria);
        $issues = $issues->filter($filter);

        if ($criteria->getSortFields()) {
            $sorter = new CriteriaSorter($criteria);
            $issues = $issues->sort($sorter);
        }

        return $issues;
    }
}
