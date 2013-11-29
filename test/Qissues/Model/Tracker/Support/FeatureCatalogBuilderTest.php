<?php

namespace Qissues\Domain\Tracker\Support;

use Qissues\Domain\Tracker\Support\FeatureCatalogBuilder;

class FeatureCatalogBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnsaCatalog()
    {
        $builder = new FeatureCatalogBuilder(array());
        $catalog = $builder->build();
        $this->assertInstanceOf('Qissues\Domain\Tracker\Support\FeatureCatalog', $catalog);
    }

    public function testAddsConfiguredFeatures()
    {
        $features = array('a', 'b', 'c');
        $builder = new FeatureCatalogBuilder($features);
        $catalog = $builder->build();

        foreach ($features as $feature) {
            $this->assertTrue((bool)$catalog->get($feature));
        }
    }
}
