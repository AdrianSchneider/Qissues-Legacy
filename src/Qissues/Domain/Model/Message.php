<?php

namespace Qissues\Domain\Model;

class Message
{
    protected $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function __toString()
    {
        return $this->message;
    }
}
