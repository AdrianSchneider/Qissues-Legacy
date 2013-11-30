<?php

namespace Qissues\Domain\Model;

use Qissues\Domain\Shared\Status;
use Qissues\Domain\Shared\Details;

class Transition
{
    protected $status;
    protected $details;

    public function __construct(Status $status, Details $details)
    {
        $this->status = $status;
        $this->details = $details;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getDetails()
    {
        return $this->details;
    }
}
