<?php

namespace Qissues\Domain\Service;

use Qissues\Domain\Model\IssueRepository;
use Qissues\Domain\Model\Request\IssueChanges;

class EditIssue
{
    protected $repository;

    public function __construct(IssueRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(IssueChanges $changes)
    {
        $this->repository->update(
            $changes->getChanges(),
            $changes->getIssue()
        );
    }
}
