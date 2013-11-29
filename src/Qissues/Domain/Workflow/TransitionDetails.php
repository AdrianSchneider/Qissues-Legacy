<?php

namespace Qissues\Model\Workflow;

class TransitionDetails
{
    public function __construct($details = null)
    {
        $this->details = $details;
    }

    public function getDetails()
    {
        return $this->details;
    }
}
