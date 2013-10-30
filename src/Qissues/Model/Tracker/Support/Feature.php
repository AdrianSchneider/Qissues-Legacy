<?php

namespace Qissues\Model\Tracker\Support;

class Feature
{
    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }
}
