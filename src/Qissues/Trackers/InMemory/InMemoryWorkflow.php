<?php

namespace Qissues\Trackers\InMemory;

use Qissues\Domain\Model\Number;
use Qissues\Domain\Model\Transition;
use Qissues\Domain\Model\Workflow;
use Qissues\Domain\Model\IssueRepository;
use Qissues\Domain\Shared\Details;
use Qissues\Domain\Shared\RequiredDetails;
use Qissues\Domain\Shared\Status;

class InMemoryWorkflow implements Workflow
{
    protected $repository;
    protected $requireFields;

    public function __construct(IssueRepository $repository, array $requireFields = array())
    {
        $this->repository = $repository;
        $this->requireFields = $requireFields;
    }

    public function changeRequiredFields(array $fields)
    {
        $this->requireFields = $fields;
    }

    /**
     * {@inheritDoc}
     */
    public function buildTransition(Number $issue, Status $status, $builder = null)
    {
        if ($this->requireFields) {
            return new Transition(
                $status,
                call_user_func($builder, new RequiredDetails($this->requireFields))
            );
        }

        return new Transition($status, new Details);
    }

    /**
     * {@inheritDoc}
     */
    public function apply(Transition $transition, Number $number)
    {
        $this->repository->changeStatus(
            $number,
            $transition->getStatus(),
            $transition->getDetails()->getDetails()
        );
    }
}
