<?php

namespace Qissues\Model\Meta;

class Field
{
    public function __construct($name, $default = null, array $options = array(), $help = null)
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

    public function __toString()
    {
        return $this->name;
    }
}
