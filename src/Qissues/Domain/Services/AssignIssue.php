<?php

namespace Qissues\Model\Services;

use Qissues\Model\Querying\Number;
use Qissues\Model\Core\Meta\User;
use Qissues\Model\Tracker\IssueRepository;

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
