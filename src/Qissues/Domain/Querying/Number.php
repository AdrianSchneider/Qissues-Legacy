<?php

namespace Qissues\Model\Querying;

class Number
{
    protected $number;

    public function __construct($number)
    {
        $this->number = $number;
    }

    public function getNumber()
    {
        return $this->number;
    }

    public function __toString()
    {
        return (string)$this->number;
    }
}
