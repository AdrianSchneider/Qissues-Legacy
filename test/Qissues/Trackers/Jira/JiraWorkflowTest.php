<?php

namespace Qissues\Trackers\Jira;

use Qissues\Domain\Model\Issue;
use Qissues\Domain\Model\Number;
use Qissues\Domain\Meta\Status;
use Qissues\Trackers\Jira\JiraWorkflow;
use Qissues\Domain\Workflow\Transition;
use Qissues\Domain\Workflow\TransitionDetails;
use Qissues\Domain\Workflow\TransitionRequirements;

class JiraWorkflowTest extends \PHPUnit_Framework_TestCase
{
    public function testApplyTransitionThrowsExceptionIfUnsupported()
    {
        $repository = $this->getMockBuilder('Qissues\Trackers\Jira\JiraRepository')->disableOriginalConstructor()->getMock();
        $repository
            ->expects($this->once())
            ->method('lookupTransitions')
            ->with($this->isInstanceOf('Qissues\Domain\Model\Number'))
            ->will($this->returnValue(array(
                array('id' => 1, 'to' => array('id' => 1, 'name' => 'dupe')),
                array('id' => 2, 'to' => array('id' => 1, 'name' => 'nonsensical'))
            )))
        ;

        $transition = new Transition(
            $this->getIssue(5, new Status('closed')),
            new Status('closed')
        );

        $this->setExpectedException('Qissues\Domain\Workflow\UnsupportedTransitionException', "dupe, nonsensical");

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
            ->with($this->isInstanceOf('Qissues\Domain\Model\Number'))
            ->will($this->returnValue(array(
                array('id' => 1, 'to' => array('id' => 1, 'name' => 'closed'))
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
            ->with($this->isInstanceOf('Qissues\Domain\Model\Number'))
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

    public function testGetsOptionsForRequirements()
    {
        $repository = $this->getMockBuilder('Qissues\Trackers\Jira\JiraRepository')->disableOriginalConstructor()->getMock();
        $repository
            ->expects($this->once())
            ->method('lookupTransitions')
            ->with($this->isInstanceOf('Qissues\Domain\Model\Number'))
            ->will($this->returnValue(array(
                array(
                    'to' => array('name' => 'closed'),
                    'fields' => array(
                        'resolution' => array(
                            'required' => true,
                            'allowedValues' => array(
                                array('name' => 'fixed'),
                                array('name' => 'done')
                            )
                        )
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
        $fields = $requirements->getFields();

        $this->assertEquals(array('fixed', 'done'), $fields[0]->getOptions());
    }

    protected function getIssue($id, $status)
    {
        return new Issue($id, '', '', $status, new \DateTime, new \DateTime);
    }
}
