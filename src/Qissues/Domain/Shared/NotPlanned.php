<?php

namespace Qissues\Domain\Shared;

class NotPlanned extends Milestone
{
    public function __construct()
    {
        $this->id = -1;
    }

    public function __toString()
    {
        return "not planned";
    }
}
