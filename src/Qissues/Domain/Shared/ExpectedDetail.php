<?php

namespace Qissues\Domain\Shared;

class ExpectedDetail
{
    protected $name;
    protected $default;
    protected $options;
    protected $help;

    /**
     *@param string       $name      field name
     *@param string       $default   value
     *@param array|null   $options   allowed options
     *@param string|null  $help      inline help
     */
    public function __construct($name, $default = '', array $options = array(), $help = null)
    {
        $this->name = $name;
        $this->default = $default;
        $this->options = $options;
        $this->help = $help;
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

    public function getHelp()
    {
        return $this->help;
    }
}
