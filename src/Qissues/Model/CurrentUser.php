<?php

namespace Qissues\Model;

class CurrentUser extends User
{
    public function __construct()
    {
        parent::__construct(-1, 'me');
    }
}
