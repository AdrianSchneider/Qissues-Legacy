<?php

namespace Qissues\Domain\Service;

use Qissues\Domain\Model\IssueRepository;
use Qissues\Domain\Model\Request\IssuePlan;

class PlanIssue
{
    protected $repository;

    /**
     * @param IssueRepository $repository
     */
    public function __construct(IssueRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Plan an issue for a milestone
     *
     * @param IssuePlan
     */
    public function __invoke(IssuePlan $plan)
    {
        $this->repository->plan(
            $plan->getIssue(),
            $plan->getMilestone()
        );
    }
}
