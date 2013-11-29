<?php

namespace Qissues\Model\Core\Meta;

class ClosedStatus extends Status
{
    public function __construct()
    {
        parent::__construct('closed');
    }
}
