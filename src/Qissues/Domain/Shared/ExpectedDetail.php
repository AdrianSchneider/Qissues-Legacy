<?php

namespace Qissues\Domain\Shared;

class ExpectedDetail
{
    protected $name;
    protected $default;
    protected $options;

    /**
     *@param string       $name      field name
     *@param string       $default   value
     *@param array|null   $options   allowed options
     */
    public function __construct($name, $default = '', array $options = array())
    {
        $this->name = $name;
        $this->default = $default;
        $this->options = $options;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDefault()
    {
        return $this->default;
    }

    public function getOptions()
    {
        return $this->options;
    }
}
