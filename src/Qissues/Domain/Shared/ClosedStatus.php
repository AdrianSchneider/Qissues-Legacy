<?php

namespace Qissues\Domain\Shared;

class ClosedStatus extends Status
{
    public function __construct()
    {
        parent::__construct('closed');
    }
}
