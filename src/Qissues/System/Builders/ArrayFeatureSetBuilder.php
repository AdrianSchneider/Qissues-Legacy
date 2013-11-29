<?php

namespace Qissues\System\Builders;

use Qissues\Trackers\Shared\Support\Feature;
use Qissues\Trackers\Shared\Support\FeatureSet;
use Qissues\Trackers\Shared\Support\FeatureSetBuilder;
use Qissues\Trackers\Shared\Support\FeatureCatalog;
use Qissues\Trackers\Shared\Support\SupportLevel;

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
