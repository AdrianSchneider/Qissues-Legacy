<?php

namespace Qissues\System\Builders;

use Qissues\Domain\Tracker\Support\Feature;
use Qissues\Domain\Tracker\Support\FeatureSet;
use Qissues\Domain\Tracker\Support\FeatureSetBuilder;
use Qissues\Domain\Tracker\Support\FeatureCatalog;
use Qissues\Domain\Tracker\Support\SupportLevel;

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
