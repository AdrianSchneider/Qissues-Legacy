<?php

namespace Qissues\Model\Tracker\Support;

class FeatureSet
{
    protected $features;

    /**
     * Adds support for Feature
     * @param Feature $feature
     * @param SupportLevel $level
     */
    public function add(Feature $feature, SupportLevel $level)
    {
        $this->features[$feature->getName()] = $level;
    }

    /**
     * Check to see if the feature set supports Feature at Level
     * @param Feature $feature
     * @param integer $level
     * @return boolean true if supported
     */
    public function supports(Feature $feature, $level)
    {
        if (!isset($this->features[$feature->getName()])) {
            return false;
        }

        return $this->features[$feature->getName()]->supports($level);
    }
}
