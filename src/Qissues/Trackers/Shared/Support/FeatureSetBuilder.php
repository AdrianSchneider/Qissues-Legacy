<?php

namespace Qissues\Trackers\Shared\Support;

interface FeatureSetBuilder
{
    function buildFor(FeatureCatalog $catalog);
}
