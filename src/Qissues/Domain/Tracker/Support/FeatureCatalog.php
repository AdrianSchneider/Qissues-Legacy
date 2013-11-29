<?php

namespace Qissues\Domain\Tracker\Support;

class FeatureCatalog
{
    protected $features = array();

    public function add(Feature $feature)
    {
        $this->features[$feature->getName()] = $feature;
    }

    public function get($name)
    {
        if (!isset($this->features[$name])) {
            throw new \BadMethodCallException("'$name' is not a valid feature");
        }

        return $this->features[$name];
    }
}
