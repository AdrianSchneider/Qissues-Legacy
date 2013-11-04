<?php

namespace Qissues\Trackers\GitHub;

use Qissues\Model\Tracker\Support\Feature;
use Qissues\Model\Tracker\Support\FeatureSet;
use Qissues\Model\Tracker\Support\FeatureSetBuilder;
use Qissues\Model\Tracker\Support\FeatureCatalog;
use Qissues\Model\Tracker\Support\SupportLevel;

class GitHubSupport implements FeatureSetBuilder
{
    /**
     * {@inheritDoc}
     */
    public function buildFor(FeatureCatalog $catalog)
    {
        $features = new FeatureSet($catalog);
        $features->add($catalog->get('statuses'),   $this->level('single'));
        $features->add($catalog->get('labels'),     $this->level('multiple', 'dynamic'));
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
