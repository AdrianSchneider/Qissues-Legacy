<?php

namespace Qissues\System\Builders;

use Qissues\Domain\Tracker\Support\FeatureCatalogBuilder;
use Qissues\System\Builders\ArrayFeatureSetBuilder;

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
        $this->assertInstanceOf('Qissues\Domain\Tracker\Support\FeatureSet', $features);
    }

    protected function getCatalog(array $features)
    {
        $builder = new FeatureCatalogBuilder($features);
        return $builder->build();
    }
}
