<?php

namespace Qissues\Domain\Model;

use Qissues\Domain\Shared\Status;

interface Workflow
{
    /**
     * Constructs a new transition for issue moving to status
     *
     * @param Number $issue
     * @param Status $status
     * @param Callable $builder
     * @return Transition
     */
    function buildTransition(Number $issue, Status $status, /*Callable*/ $builder = null);

    /**
     * Applies a transition to an issue
     *
     * @param Transition
     * @param Issue
     */
    function apply(Transition $transition, Number $issue);
}
