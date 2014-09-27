<?php

namespace Qissues\Domain\Shared;

class Milestone
{
    public function __construct($id, $name, \DateTime $date)
    {
        $this->id = $id;
        $this->name = $name;
        $this->date = $date;
    }
}
