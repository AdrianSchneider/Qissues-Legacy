<?php

namespace Qissues\Model\Workflow;

use Qissues\Model\Issue;
use Qissues\Model\Meta\Status;
use Qissues\Model\Querying\Number;
use Qissues\Model\Workflow\BasicWorkflow;
use Qissues\Model\Workflow\TransitionDetails;

class BasicWorkflowTest extends \PHPUnit_Framework_TestCase
{
    public function testAllowsAnything()
    {
        $transition = new Transition(
            new Issue(1, 'title', 'desc', new Status('open'), new \DateTime, new \DateTime),
            new Status('closed')
        );

        $transitioner = $this->getMock('Qissues\Model\Workflow\BasicTransitioner');
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
        $transitioner = $this->getMock('Qissues\Model\Workflow\BasicTransitioner');

        $transition = new Transition(
            new Issue(1, 'title', 'desc', new Status('open'), new \DateTime, new \DateTime),
            new Status('closed')
        );

        $workflow = new BasicWorkflow($transitioner);
        $requirements = $workflow->getRequirements($transition);

        $this->assertEquals(array(), $requirements->getFields());
    }
}
