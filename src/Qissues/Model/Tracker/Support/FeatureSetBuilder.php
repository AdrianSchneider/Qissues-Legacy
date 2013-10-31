<?php

namespace Qissues\Model\Tracker\Support;

interface FeatureSetBuilder
{
    function buildFor(FeatureCatalog $catalog);
}
