<?php

namespace Qissues\Application\Container;

use Qissues\Application\Tracker\Support\Feature;
use Qissues\Application\Tracker\Support\FeatureCatalog;

class ArrayCatalogBuilder
{
    protected $features;

    /**
     * @param array features to add
     */
    public function __construct(array $features)
    {
        $this->features = $features;
    }

    /**
     * Creates a FeatureCatalog based on configured features
     * @return FeatureCatalog
     */
    public function build()
    {
        $catalog = new FeatureCatalog();
        foreach ($this->features as $feature) {
            $catalog->add(new Feature($feature));
        }

        return $catalog;
    }
}
