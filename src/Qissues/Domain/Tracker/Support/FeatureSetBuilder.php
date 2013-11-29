<?php

namespace Qissues\Domain\Tracker\Support;

interface FeatureSetBuilder
{
    function buildFor(FeatureCatalog $catalog);
}
