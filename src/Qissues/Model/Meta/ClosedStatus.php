<?php

namespace Qissues\Model\Meta;

class ClosedStatus extends Status
{
    public function __construct()
    {
        parent::__construct('closed');
    }
}
