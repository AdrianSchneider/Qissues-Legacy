<?php

namespace Qissues\Trackers\Jira;

use Qissues\Model\Issue;
use Qissues\Model\Querying\Number;
use Qissues\Model\Meta\Status;
use Qissues\Trackers\Jira\JiraWorkflow;
use Qissues\Model\Workflow\Transition;
use Qissues\Model\Workflow\TransitionDetails;
use Qissues\Model\Workflow\TransitionRequirements;

class JiraWorkflowTest extends \PHPUnit_Framework_TestCase
{
    public function testApplyTransitionThrowsExceptionIfUnsupported()
    {
        $repository = $this->getMockBuilder('Qissues\Trackers\Jira\JiraRepository')->disableOriginalConstructor()->getMock();
        $repository
            ->expects($this->once())
            ->method('lookupTransitions')
            ->with($this->isInstanceOf('Qissues\Model\Querying\Number'))
            ->will($this->returnValue(array()))
        ;

        $transition = new Transition(
            $this->getIssue(5, new Status('closed')),
            new Status('closed')
        );

        $this->setExpectedException('Qissues\Model\Workflow\UnsupportedTransitionException');

        $workflow = new JiraWorkflow($repository);
        $workflow->apply($transition, new TransitionDetails(array()));
    }

    public function testsAllowedTransitions()
    {
        $repository = $this->getMockBuilder('Qissues\Trackers\Jira\JiraRepository')->disableOriginalConstructor()->getMock();

        $transition = new Transition(
            $this->getIssue(5, new Status('open')),
            new Status('closed')
        );

        $details = new TransitionDetails($deets = array('a' => 'b'));

        $repository
            ->expects($this->once())
            ->method('lookupTransitions')
            ->with($this->isInstanceOf('Qissues\Model\Querying\Number'))
            ->will($this->returnValue(array(
                array(
                    'id' => 1,
                    'to' => array('id' => 1, 'name' => 'closed')
                )
            )))
        ;
        $repository
            ->expects($this->once())
            ->method('changeStatus')
            ->with(new Number(5), new Status('closed'), 1, $deets)
        ;

        $workflow = new JiraWorkflow($repository);
        $workflow->apply($transition, $details);
    }

    public function testGetsOnlyRequirements()
    {
        $repository = $this->getMockBuilder('Qissues\Trackers\Jira\JiraRepository')->disableOriginalConstructor()->getMock();
        $repository
            ->expects($this->once())
            ->method('lookupTransitions')
            ->with($this->isInstanceOf('Qissues\Model\Querying\Number'))
            ->will($this->returnValue(array(
                array(
                    'to' => array('name' => 'closed'),
                    'fields' => array(
                        'resolution' => array('required' => true),
                        'stupidfield' => array('required' => false)
                    )
                )
            )))
        ;

        $transition = new Transition(
            $this->getIssue(5, new Status('open')),
            new Status('closed')
        );

        $workflow = new JiraWorkflow($repository);
        $requirements = $workflow->getRequirements($transition);

        $this->assertEquals(array('resolution'), $requirements->getFields());
    }

    protected function getIssue($id, $status)
    {
        return new Issue($id, '', '', $status, new \DateTime, new \DateTime);
    }
}
