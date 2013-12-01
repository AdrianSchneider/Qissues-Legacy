<?php

namespace Qissues\Application\Container;

use Qissues\Application\Container\ArrayCatalogBuilder;
use Qissues\Application\Container\ArrayFeatureSetBuilder;

class ArrayFeatureSetBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testBuilds()
    {
        $catalog = $this->getCatalog(array('main', 'secondary'));
        $builder = new ArrayFeatureSetBuilder();

        $features = $builder->build($catalog, array(
            'main' => array('multiple', 'dynamic'),
            'secondary' => array()
        ));
        $this->assertInstanceOf('Qissues\Trackers\Shared\Support\FeatureSet', $features);
    }

    protected function getCatalog(array $features)
    {
        $builder = new ArrayCatalogBuilder($features);
        return $builder->build();
    }
}
