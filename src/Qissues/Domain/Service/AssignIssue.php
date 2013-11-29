<?php

namespace Qissues\Domain\Service;

use Qissues\Domain\Model\Number;
use Qissues\Domain\Model\IssueRepository;
use Qissues\Domain\Shared\User;

class AssignIssue
{
    protected $repository;

    public function __construct(IssueRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Posts a comment to an issue
     *
     * @param User $user
     * @param Number $issue
     */
    public function __invoke(User $user, Number $number)
    {
        $this->repository->assign($number, $user);
    }
}
