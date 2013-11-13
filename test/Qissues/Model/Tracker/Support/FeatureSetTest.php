<?php

namespace Qissues\Tests\Model\Tracker\Support;

use Qissues\Model\Tracker\Support\Feature;
use Qissues\Model\Tracker\Support\FeatureSet;
use Qissues\Model\Tracker\Support\SupportLevel;

class FeatureSetTest extends \PHPUnit_Framework_TestCase
{
    public function testCanCheckFeatureFromSet()
    {
        $feature = new Feature('something');
        $level = new SupportLevel();
        $level->set('single');

        $features = new FeatureSet();
        $features->add($feature, $level);

        $this->assertTrue($features->supports('something', SupportLevel::SINGLE));
    }

    public function testReturnsFalseIfNotAdded()
    {
        $features = new FeatureSet();
        $this->assertFalse($features->supports('made up', SupportLevel::SINGLE));
    }
}
