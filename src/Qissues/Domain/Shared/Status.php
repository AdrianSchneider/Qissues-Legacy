<?php

namespace Qissues\Domain\Shared;

class Status
{
    protected $status;
    protected $id;
    protected $name;

    public function __construct($status, $id = null, $name = null)
    {
        $this->status = $status;
        $this->id = $id;
        $this->name = $name;
    }

    public function getStatus()
    {
        return $this->status;
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
        return $this->status;
    }
}
