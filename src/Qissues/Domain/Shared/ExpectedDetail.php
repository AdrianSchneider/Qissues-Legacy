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
    public function __construct($name, $required = true, $default = '', array $options = array())
    {
        $this->name = $name;
        if ($required !== true and $required !== false) {
            throw new \Exception('$required must be boolean (refactor');
        }

        $this->default = $default;
        $this->required = !!$required;
        $this->options = $options;
    }

    public function isRequired()
    {
        return $this->required;
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
