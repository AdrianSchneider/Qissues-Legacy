<?php

namespace Qissues\Domain\Shared;

class RequiredDetails
{
    protected $fields;

    public function __construct(array $fields = array())
    {
        $this->fields = $fields;
    }

    public function getFields()
    {
        return $this->fields;
    }
}
