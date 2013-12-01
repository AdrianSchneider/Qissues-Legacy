<?php

namespace Qissues\Application\Tracker;

use Qissues\Domain\Model\Number;
use Qissues\Domain\Model\Transition;
use Qissues\Domain\Model\Workflow;
use Qissues\Domain\Shared\Status;
use Qissues\Domain\Shared\Details;
use Qissues\Application\Tracker\BasicWorkflow;

class BasicWorkflowTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildTransition()
    {
        $workflow = new BasicWorkflow(
            $this->getMock('Qissues\Application\Tracker\BasicTransitioner')
        );

        $transition = $workflow->buildTransition(
            new Number(1),
            $status = new Status('closed')
        );

        $this->assertInstanceOf('Qissues\Domain\Model\Transition', $transition);
        $this->assertSame($status, $transition->getStatus());
        $this->assertEmpty($transition->getDetails()->getDetails());
    }

    public function testApplyDelegatesToTransitioner()
    {
        $number = new Number(1);
        $status = new Status('closed');

        $transitioner = $this->getMock('Qissues\Application\Tracker\BasicTransitioner');
        $transitioner
            ->expects($this->once())
            ->method('changeStatus')
            ->with($number, $status)
        ;

        $workflow = new BasicWorkflow($transitioner);
        $workflow->apply(new Transition($status, new Details), $number);
    }

    public function testGetRequirementsReturnsEmpty()
    {
        $transition = new Transition(new Status('closed'), new Details);

        $workflow = new BasicWorkflow($this->getMock('Qissues\Application\Tracker\BasicTransitioner'));
        $requirements = $workflow->getRequirements($transition);

        $this->assertEmpty($requirements->getFields());
    }
}
