<?php

namespace Qissues\Model\Core\Meta;

class OpenStatus extends Status
{
    public function __construct()
    {
        parent::__construct('open');
    }
}
