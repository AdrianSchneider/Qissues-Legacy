<?php

namespace Qissues\Tests\Model\Tracker\Support;

use Qissues\Domain\Tracker\Support\Feature;
use Qissues\Domain\Tracker\Support\FeatureSet;
use Qissues\Domain\Tracker\Support\SupportLevel;

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

    public function testDoesSupportReturnsTrueIfSupported()
    {
        $feature = new Feature('something');
        $level = new SupportLevel();
        $level->set('single');

        $features = new FeatureSet();
        $features->add($feature, $level);

        $this->assertTrue($features->doesSupport('something'));
    }

    public function testDoesSupportReturnsFalseIfSupported()
    {
        $features = new FeatureSet();
        $this->assertFalse($features->doesSupport('anything'));
    }
}
