<?php

namespace Qissues\Model\Tracker;

use Qissues\Model\Tracker\IssueTracker;

class IssueTrackerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $tracker = new IssueTracker(
            $repository = $this->getMock('Qissues\Model\Tracker\IssueRepository'),
            $mapping = $this->getMock('Qissues\Model\Tracker\FieldMapping'),
            $featureSet = $this->getMockBuilder('Qissues\Model\Tracker\Support\FeatureSet')->disableOriginalConstructor()->getMock()
        );

        $this->assertSame($repository, $tracker->getRepository());
        $this->assertSame($mapping, $tracker->getMapping());
        $this->assertSame($featureSet, $tracker->getFeatures());
    }
}
