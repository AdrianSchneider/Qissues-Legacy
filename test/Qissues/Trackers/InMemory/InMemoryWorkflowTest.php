<?php

namespace Qissues\Trackers\InMemory;

use Qissues\Domain\Model\Number;
use Qissues\Domain\Model\Transition;
use Qissues\Domain\Shared\Details;
use Qissues\Domain\Shared\Status;
use Qissues\Domain\Shared\RequiredDetails;
use Qissues\Trackers\InMemory\InMemoryWorkflow;

class InMemoryWorkflowTest extends \PHPUnit_Framework_TestCase
{
    public function testBuild()
    {
        $issue = new Number(1);
        $status = new Status('closed');

        $workflow = new InMemoryWorkflow($this->getMock('Qissues\Domain\Model\IssueRepository'));
        $transition = $workflow->buildTransition($issue, $status);

        $this->assertSame($status, $transition->getStatus());
        $this->assertEmpty($transition->getDetails()->getDetails());
    }

    public function testBuildWithDetails()
    {
        $issue = new Number(1);
        $status = new Status('closed');

        $workflow = new InMemoryWorkflow(
            $this->getMock('Qissues\Domain\Model\IssueRepository'), 
            array('reason')
        );
        $transition = $workflow->buildTransition($issue, $status, function(RequiredDetails $requirements) {
            return new Details(array('reason' => 'ugh, callbacks'));
        });

        $this->assertSame($status, $transition->getStatus());
        $this->assertEquals(array('reason' => 'ugh, callbacks'), $transition->getDetails()->getDetails());
    }

    public function testApply()
    {
        $issue = new Number(1);
        $status = new Status('closed');

        $workflow = new InMemoryWorkflow($repo = $this->getMock('Qissues\Trackers\InMemory\InMemoryRepository'));
        $repo
            ->expects($this->once())
            ->method('changeStatus')
            ->with($issue, $status)
        ;

        $transition = new Transition($status, new Details);
        $workflow->apply($transition, $issue);
    }

    public function testApplyWithDetails()
    {
        $issue = new Number(1);
        $status = new Status('closed');
        $details = array('reason' => 'ugh');

        $workflow = new InMemoryWorkflow(
            $repo = $this->getMock('Qissues\Trackers\InMemory\InMemoryRepository'),
            array('reason')
        );

        $repo
            ->expects($this->once())
            ->method('changeStatus')
            ->with($issue, $status, $details)
        ;

        $transition = new Transition($status, new Details($details));
        $workflow->apply($transition, $issue);
    }
}
