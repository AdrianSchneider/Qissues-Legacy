<?php

namespace Qissues\Model\Meta;

use Qissues\System\DataType\ReadOnlyArrayAccess;

class User extends ReadOnlyArrayAccess
{
    public function __construct($account, $id = null, $name = null)
    {
        $this->account = $account;
        $this->id = $id;
        $this->name = $name;
    }

    public function getAccount()
    {
        return $this->account;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function __toString()
    {
        return $this->account;
    }
}
