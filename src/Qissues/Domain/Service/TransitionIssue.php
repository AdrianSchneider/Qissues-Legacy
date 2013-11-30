<?php

namespace Qissues\Domain\Service;

use Qissues\Domain\Model\Workflow;
use Qissues\Domain\Model\IssueRepository;
use Qissues\Domain\Model\Request\IssueTransition;
use Qissues\Domain\Model\Request\NewComment;

class TransitionIssue
{
    protected $workflow;
    protected $repository;

    /**
     * @param Workflow $workflow
     * @param IssueRepository $repository
     */
    public function __construct(Workflow $workflow, IssueRepository $repository)
    {
        $this->workflow = $workflow;
        $this->repository = $repository;
    }

    /**
     * Transitions an issue from one status to another
     *
     * @param IssueTransition $transition
     */
    public function __invoke(IssueTransition $request)
    {
        $this->workflow->apply(
            $request->getTransition(),
            $request->getIssue()
        );

        if ($comment = $request->getComment()) {
            $this->repository->comment(
                $request->getIssue(),
                $request->getComment()
            );
        }
    }
}
