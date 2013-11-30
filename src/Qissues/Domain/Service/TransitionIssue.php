<?php

namespace Qissues\Domain\Service;

use Qissues\Domain\Model\Workflow;
use Qissues\Domain\Model\IssueRepository;
use Qissues\Domain\Model\Request\IssueTransition;

class TransitionIssue
{
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
            $this->workflow->comment(new NewComment($comment));
        }
    }
}
