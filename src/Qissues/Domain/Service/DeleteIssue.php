<?php

namespace Qissues\Domain\Service;

use Qissues\Domain\Model\Number;
use Qissues\Domain\Model\IssueRepository;

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
