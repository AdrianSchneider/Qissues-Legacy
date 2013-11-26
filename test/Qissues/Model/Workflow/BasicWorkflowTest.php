<?php

namespace Qissues\Model\Workflow;

use Qissues\Model\Workflow\BasicWorkflow;

class BasicWorkflowTest extends \PHPUnit_Framework_TestCase
{
    public function testAllowsAnything()
    {
        $transition = $this->getMockBuilder('Qissues\Model\Workflow\Transition')->disableOriginalConstructor()->getMock();

        $workflow = new BasicWorkflow();
        $result = $workflow->supports($transition);

        $this->assertTrue($result);
    }

    public function testHasNoRequirements()
    {
        $transition = $this->getMockBuilder('Qissues\Model\Workflow\Transition')->disableOriginalConstructor()->getMock();

        $workflow = new BasicWorkflow();
        $requirements = $workflow->getRequirements($transition);

        $this->assertEmpty($requirements);
    }
}
