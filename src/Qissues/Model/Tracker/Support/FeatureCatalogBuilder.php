<?php

namespace Qissues\Model\Tracker\Support;

class FeatureCatalogBuilder
{
    public function build()
    {
        $catalog = new FeatureCatalog();
        $catalog->add(new Feature('types'));
        $catalog->add(new Feature('statuses'));
        $catalog->add(new Feature('labels'));
        $catalog->add(new Feature('priorities'));
        return $catalog;
    }
}
