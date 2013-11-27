<?php

namespace Qissues\Model\Workflow;

use Qissues\Model\Meta\Status;
use Qissues\Model\Querying\Number;

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
