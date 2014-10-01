<?php

namespace Qissues\Domain\Shared;

class Milestone
{
    public function __construct($name, $id = null, \DateTime $date = null)
    {
        $this->name = $name;
        $this->id = $id;
        $this->date = $date;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getId()
    {
        return $this->id;
    }
}
