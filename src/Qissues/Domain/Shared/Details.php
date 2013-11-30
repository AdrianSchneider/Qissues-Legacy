<?php

namespace Qissues\Domain\Shared;

class Details
{
    protected $details;

    public function __construct(array $details = array())
    {
        $this->details = $details;
    }

    public function getDetails()
    {
        return $this->details;
    }
}
