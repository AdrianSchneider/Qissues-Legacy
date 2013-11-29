<?php

namespace Qissues\Application\Tracker\Support;

interface FeatureSetBuilder
{
    function buildFor(FeatureCatalog $catalog);
}
