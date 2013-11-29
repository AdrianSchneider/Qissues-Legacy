<?php

namespace Qissues\Domain\Service;

use Qissues\Domain\Model\IssueRepository;
use Qissues\Domain\Model\Request\IssueAssignment;

class AssignIssue
{
    protected $repository;

    public function __construct(IssueRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Assign a user to an issue, optionally commenting
     *
     * @param IssueAssignment
     */
    public function __invoke(IssueAssignment $assignment)
    {
        $this->repository->assign(
            $assignment->getIssue(),
            $assignment->getAssignee()
        );

        if ($comment = $assignment->getComment()) {
            $this->repository->coment($comment);
        }
    }
}
