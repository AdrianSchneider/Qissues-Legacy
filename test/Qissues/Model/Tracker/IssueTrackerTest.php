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
            $featureSet = $this->getMockBuilder('Qissues\Model\Tracker\Support\FeatureSet')->disableOriginalConstructor()->getMock(),
            $workflow = $this->getMock('Qissues\Model\Workflow\Workflow')
        );

        $this->assertSame($repository, $tracker->getRepository());
        $this->assertSame($mapping, $tracker->getMapping());
        $this->assertSame($featureSet, $tracker->getFeatures());
        $this->assertSame($workflow, $tracker->getWorkflow());
    }
}
