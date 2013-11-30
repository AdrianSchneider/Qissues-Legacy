<?php

namespace Qissues\Trackers\Shared;

use Qissues\Domain\Model\Number;
use Qissues\Domain\Shared\Status;

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
