<?php

namespace Qissues\Model\Workflow;

interface Workflow
{
    /**
     * Apply a Transition
     *
     * @param Transition $transition
     * @throws UnsupportedTransitionException when disallowed
     */
    function apply(Transition $transition, TransitionDetails $details);

    /**
     * Find out any require or editable fields for a transition
     *
     * @param Tarnsition $transition
     * @return UnsupportedTransitionException when disallowed
     */
    function getRequirements(Transition $transition);
}
