<?php

namespace Qissues\System\Builders;

use Qissues\Application\Tracker\Support\Feature;
use Qissues\Application\Tracker\Support\FeatureSet;
use Qissues\Application\Tracker\Support\FeatureSetBuilder;
use Qissues\Application\Tracker\Support\FeatureCatalog;
use Qissues\Application\Tracker\Support\SupportLevel;

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
