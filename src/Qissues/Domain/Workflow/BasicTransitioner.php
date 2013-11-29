<?php

namespace Qissues\Domain\Workflow;

use Qissues\Domain\Meta\Status;
use Qissues\Domain\Model\Number;

interface BasicTransitioner
{
    /**
     * Change an Issue to a new status with no constraints
     *
     * @param Number $number
     * @param Status $status
     */
    function changeStatus(Number $number, Status $status);
}
