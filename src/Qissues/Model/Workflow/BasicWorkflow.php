<?php

namespace Qissues\Model\Workflow;

use Qissues\Model\Querying\Number;

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
     * Applies a transition using a basic transitioner
     *
     * {@inheritDoc}
     */
    public function apply(Transition $transition, TransitionDetails $details)
    {
        $this->transitioner->changeStatus(
            new Number($transition->getIssue()->getId()),
            $transition->getStatus()
        );
    }

    /**
     * Basic workflows don't have requirements
     *
     * {@inheritDoc}
     */
    public function getRequirements(Transition $transition)
    {
        return new TransitionRequirements(array());
    }
}
