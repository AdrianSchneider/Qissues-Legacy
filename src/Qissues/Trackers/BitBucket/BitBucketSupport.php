<?php

namespace Qissues\Trackers\BitBucket;

use Qissues\Model\Tracker\Support\FeatureSet;
use Qissues\Model\Tracker\Support\FeatureSetBuilder;
use Qissues\Model\Tracker\Support\FeatureCatalog;
use Qissues\Model\Tracker\Support\SupportLevel;

class BitBucketSupport implements FeatureSetBuilder
{
    /**
     * {@inheritDoc}
     */
    public function buildFor(FeatureCatalog $catalog)
    {
        $features = new FeatureSet($catalog);
        $features->add($catalog->get('statuses'),   $this->level('single'));
        $features->add($catalog->get('types'),      $this->level('single'));
        $features->add($catalog->get('labels'),     $this->level('multiple', 'dynamic'));
        $features->add($catalog->get('priorities'), $this->level('single'));
        return $features;
    }

    /**
     * Define a level with multiple steps at once
     * @param string, [string, [string, ...]]
     * @return SupportLevel
     */
    protected function level($args)
    {
        $level = new SupportLevel();
        foreach (func_get_args() as $amount) { $level->set($amount); }
        return $level;
    }
}
