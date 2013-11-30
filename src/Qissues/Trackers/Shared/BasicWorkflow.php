<?php

namespace Qissues\Trackers\Shared;

use Qissues\Domain\Model\Issue;
use Qissues\Domain\Model\Number;
use Qissues\Domain\Model\Transition;
use Qissues\Domain\Model\Workflow;
use Qissues\Domain\Shared\Details;
use Qissues\Domain\Shared\RequiredDetails;
use Qissues\Domain\Shared\Status;

class BasicWorkflow implements Workflow
{
    /**
     * @param BasicTransitioner $transitioner
     */
    public function __construct(BasicTransitioner $transitioner)
    {
        $this->transitioner = $transitioner;
    }

    /**
     * {@inheritDoc}
     */
    public function buildTransition(Issue $issue, Status $status, /*Caller*/ $builder = null)
    {
        return new Transition(
            $status,
            new Details()
        );
    }

    /**
     * Applies a transition using a basic transitioner
     *
     * {@inheritDoc}
     */
    public function apply(Transition $transition, Number $issue)
    {
        $this->transitioner->changeStatus($issue, $transition->getStatus());
    }

    /**
     * Basic workflows don't have requirements any additional
     *
     * {@inheritDoc}
     */
    public function getRequirements(Transition $transition)
    {
        return new RequiredDetails();
    }
}
