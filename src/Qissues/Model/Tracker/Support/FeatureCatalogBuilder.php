<?php

namespace Qissues\Model\Tracker\Support;

class FeatureCatalogBuilder
{
    public function build()
    {
        $catalog = new FeatureCatalog();
        $catalog->add(new Feature('milestones'));
        $catalog->add(new Feature('types'));
        $catalog->add(new Feature('statuses'));
        $catalog->add(new Feature('labels'));
        return $catalog;
    }
}
