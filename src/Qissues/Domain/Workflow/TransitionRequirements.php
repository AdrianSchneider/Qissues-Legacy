<?php

namespace Qissues\Domain\Workflow;

class TransitionRequirements
{
    protected $fields;

    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    public function getFields()
    {
        return $this->fields;
    }
}
