<?php

namespace Qissues\Model\Core\Meta;

class Label
{
    protected $name;
    protected $id;
    protected $fullName;

    public function __construct($name, $id = null, $fullName = null)
    {
        $this->name = $name;
        $this->id = $id;
        $this->fullName = $fullName;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getId()
    {
        return $this->id;
    }

    public function __toString()
    {
        return $this->name;
    }
}
