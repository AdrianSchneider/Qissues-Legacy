<?php

namespace Qissues\System\Builders;

use Qissues\Model\Tracker\Support\Feature;
use Qissues\Model\Tracker\Support\FeatureSet;
use Qissues\Model\Tracker\Support\FeatureSetBuilder;
use Qissues\Model\Tracker\Support\FeatureCatalog;
use Qissues\Model\Tracker\Support\SupportLevel;

class ArrayFeatureSetBuilder
{
    public function build(FeatureCatalog $catalog, array $withFeatures)
    {
        $features = new FeatureSet($catalog);

        foreach ($withFeatures as $feature => $levels) {
            $level = new SupportLevel();
            foreach ($levels as $levelName) {
                $level->set($levelName);
            }

            $features->add($catalog->get($feature), $level);
        }

        return $features;
    }
}
