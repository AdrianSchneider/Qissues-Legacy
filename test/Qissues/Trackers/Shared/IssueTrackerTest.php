<?php

namespace Qissues\Trackers\Shared;

use Qissues\Trackers\Shared\IssueTracker;

class IssueTrackerTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $tracker = new IssueTracker(
            $repository = $this->getMock('Qissues\Domain\Model\IssueRepository'),
            $mapping = $this->getMock('Qissues\Trackers\Shared\FieldMapping'),
            $featureSet = $this->getMockBuilder('Qissues\Trackers\Shared\Support\FeatureSet')->disableOriginalConstructor()->getMock(),
            $workflow = $this->getMock('Qissues\Domain\Model\Workflow')
        );

        $this->assertSame($repository, $tracker->getRepository());
        $this->assertSame($mapping, $tracker->getMapping());
        $this->assertSame($featureSet, $tracker->getFeatures());
        $this->assertSame($workflow, $tracker->getWorkflow());
    }
}
