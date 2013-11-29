<?php

namespace Qissues\Domain\Shared;

class OpenStatus extends Status
{
    public function __construct()
    {
        parent::__construct('open');
    }
}
