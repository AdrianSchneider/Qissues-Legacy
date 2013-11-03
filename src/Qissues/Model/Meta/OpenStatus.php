<?php

namespace Qissues\Model\Meta;

class OpenStatus extends Status
{
    public function __construct()
    {
        parent::__construct('open');
    }
}
