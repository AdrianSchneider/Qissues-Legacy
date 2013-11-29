<?php

namespace Qissues\Model\Core\Meta;

class CurrentUser extends User
{
    public function __construct()
    {
        parent::__construct(-1, 'me');
    }
}
