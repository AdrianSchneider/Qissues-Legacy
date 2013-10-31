<?php

namespace Qissues\Tests\Model\Tracker\Support;

use Qissues\Model\Tracker\Support\Feature;
use Qissues\Model\Tracker\Support\FeatureCatalog;

class FeatureCatalogTest extends \PHPUnit_Framework_TestCase
{
    public function testAddAndRetrieveFeature()
    {
        $offering = new FeatureCatalog();
        $offering->add($feature = new Feature('hello world'));

        $this->assertSame($feature, $offering->get('hello world'));
    }

    public function testGetThrowsExceptionIfNotAdded()
    {
        $offering = new FeatureCatalog();

        $this->setExpectedException('BadMethodCallException');
        $offering->get('goodbye');
    }
}
