<?php

namespace Qissues\Trackers\GitHub;

use Qissues\Model\Tracker\Support\FeatureSet;
use Qissues\Model\Tracker\Support\FeatureSetBuilder;

class GitHubSupport implements FeatureSetBuilder
{
    public function build()
    {
        $features = new FeatureSet();
        $features->add(new Feature('milestones'), $this->level()->setSingle()->setDynamic());
        $features->add(new Feature('types'),      $this->level()->setSingle()->setDynamic());
        $features->add(new Feature('milestones'), $this->level()->setMultiple()->setDynamic());
        $features->add(new Feature('statuses'),   $this->level()->setSingle());
        return $features;
    }
}
