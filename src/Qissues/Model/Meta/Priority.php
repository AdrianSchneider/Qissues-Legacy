<?php

namespace Qissues\Model\Meta;

class Priority
{
    public function __construct($priority, $name)
    {
        $this->priority = $priority;
        $this->name = $name;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function getName()
    {
        return $this->name;
    }

    public function __toString()
    {
        return $this->name;
    }
}
