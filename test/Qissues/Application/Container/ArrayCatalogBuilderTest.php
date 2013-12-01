<?php

namespace Qissues\Application\Container;

use Qissues\Application\Container\ArrayCatalogBuilder;

class FeatureCatalogBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnsaCatalog()
    {
        $builder = new ArrayCatalogBuilder(array());
        $catalog = $builder->build();
        $this->assertInstanceOf('Qissues\Trackers\Shared\Support\FeatureCatalog', $catalog);
    }

    public function testAddsConfiguredFeatures()
    {
        $features = array('a', 'b', 'c');
        $builder = new ArrayCatalogBuilder($features);
        $catalog = $builder->build();

        foreach ($features as $feature) {
            $this->assertTrue((bool)$catalog->get($feature));
        }
    }
}
