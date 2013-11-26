<?php

namespace Qissues\Model\Workflow;

class BasicWorkflow implements Workflow
{
    /**
     * Basic workflow allow anything
     *
     * {@inheritDoc}
     */
    public function supports(Transition $transition)
    {
        return true;
    }

    /**
     * Basic workflow doesn't require anything
     *
     * {@inheritDoc}
     */
    public function getRequirements(Transition $transition)
    {
        return array();
    }
}
