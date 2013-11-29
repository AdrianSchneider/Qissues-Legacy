<?php

namespace Qissues\Application\Tracker\Support;

class FeatureSet
{
    protected $features = array();

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
     * @param string $featureName
     * @param string $levelName
     * @return boolean true if supported
     */
    public function supports($featureName, $levelName)
    {
        if (!isset($this->features[$featureName])) {
            return false;
        }

        return $this->features[$featureName]->supports($levelName);
    }

    /**
     * Check to see if $featureName is supported at all
     * @param string $featureName
     * @return boolean true if supported
     */
    public function doesSupport($featureName)
    {
        if (!isset($this->features[$featureName])) {
            return false;
        }

        return $this->features[$featureName]->isSupported();
    }
}
