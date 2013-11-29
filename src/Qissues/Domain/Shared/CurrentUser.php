<?php

namespace Qissues\Domain\Shared;

class CurrentUser extends User
{
    public function __construct()
    {
        parent::__construct('me', -1);
    }
}
