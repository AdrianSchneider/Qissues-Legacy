<?php

namespace Qissues\Domain\Tracker;

use Qissues\Domain\Tracker\IssueTracker;

class IssueTrackerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $tracker = new IssueTracker(
            $repository = $this->getMock('Qissues\Domain\Model\IssueRepository'),
            $mapping = $this->getMock('Qissues\Domain\Tracker\FieldMapping'),
            $featureSet = $this->getMockBuilder('Qissues\Domain\Tracker\Support\FeatureSet')->disableOriginalConstructor()->getMock(),
            $workflow = $this->getMock('Qissues\Domain\Workflow\Workflow')
        );

        $this->assertSame($repository, $tracker->getRepository());
        $this->assertSame($mapping, $tracker->getMapping());
        $this->assertSame($featureSet, $tracker->getFeatures());
        $this->assertSame($workflow, $tracker->getWorkflow());
    }
}
