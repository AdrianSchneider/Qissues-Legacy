<?php

namespace Qissues\Domain\Workflow;

use Qissues\Domain\Model\Issue;
use Qissues\Domain\Meta\Status;
use Qissues\Domain\Model\Number;
use Qissues\Domain\Workflow\BasicWorkflow;
use Qissues\Domain\Workflow\TransitionDetails;

class BasicWorkflowTest extends \PHPUnit_Framework_TestCase
{
    public function testAllowsAnything()
    {
        $transition = new Transition(
            new Issue(1, 'title', 'desc', new Status('open'), new \DateTime, new \DateTime),
            new Status('closed')
        );

        $transitioner = $this->getMock('Qissues\Domain\Workflow\BasicTransitioner');
        $transitioner
            ->expects($this->once())
            ->method('changeStatus')
            ->with(
                $this->callback(function($number) {
                    return $number->getNumber() == 1;
                }),
                $this->callback(function($status) {
                    return $status->getStatus() == 'closed';
                })
            )
        ;

        $workflow = new BasicWorkflow($transitioner);
        $workflow->apply($transition, new TransitionDetails(array()));
    }

    public function testHasNoRequirements()
    {
        $transitioner = $this->getMock('Qissues\Domain\Workflow\BasicTransitioner');

        $transition = new Transition(
            new Issue(1, 'title', 'desc', new Status('open'), new \DateTime, new \DateTime),
            new Status('closed')
        );

        $workflow = new BasicWorkflow($transitioner);
        $requirements = $workflow->getRequirements($transition);

        $this->assertEquals(array(), $requirements->getFields());
    }
}
