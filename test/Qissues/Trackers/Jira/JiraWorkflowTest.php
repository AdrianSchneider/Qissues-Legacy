<?php

namespace Qissues\Trackers\Jira;

use Qissues\Domain\Model\Number;
use Qissues\Domain\Shared\Status;
use Qissues\Trackers\Jira\JiraWorkflow;
use Qissues\Domain\Model\Transition;
use Qissues\Domain\Shared\Details;
use Qissues\Domain\Shared\ExpectedDetails;

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

        $transition = new Transition(new Status('closed'), new Details);

        $this->setExpectedException('Qissues\Domain\Model\Exception\MappingException', "dupe, nonsensical");

        $workflow = new JiraWorkflow($repository);
        $workflow->apply($transition, new Number(5));
    }

    public function testsAllowedTransitions()
    {
        $issue = new Number(5);
        $status = new Status('closed');
        $details = new Details();

        $repository = $this->getMockBuilder('Qissues\Trackers\Jira\JiraRepository')->disableOriginalConstructor()->getMock();
        $repository
            ->expects($this->once())
            ->method('lookupTransitions')
            ->with($this->isInstanceOf('Qissues\Domain\Model\Number'))
            ->will($this->returnValue(array(
                array('id' => 1, 'to' => array('id' => 1, 'name' => 'open')),
                array('id' => 2, 'to' => array('id' => 1, 'name' => 'closed'))
            )))
        ;
        $repository
            ->expects($this->once())
            ->method('changeStatus')
            ->with($issue, $status, 2, $details);

        $transition = new Transition($status, $details);

        $workflow = new JiraWorkflow($repository);
        $workflow->apply($transition, $issue);
    }

    public function testBuildsTransitionWithEmptyDetails()
    {
        $test = $this;

        $repository = $this->getMockBuilder('Qissues\Trackers\Jira\JiraRepository')->disableOriginalConstructor()->getMock();
        $repository
            ->expects($this->once())
            ->method('lookupTransitions')
            ->with($this->isInstanceOf('Qissues\Domain\Model\Number'))
            ->will($this->returnValue(array(
                array('to' => array('name' => 'closed'), 'fields' => array())
            )))
        ;

        $workflow = new JiraWorkflow($repository);
        $transition = $workflow->buildTransition(new Number(1), new Status('closed'));

        $this->assertEmpty($transition->getDetails()->getDetails());
    }

    public function testBuildsTransitionWithRequiredFields()
    {
        $test = $this;

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

        $workflow = new JiraWorkflow($repository);
        $transition = $workflow->buildTransition(
            new Number(1),
            new Status('closed'),
            function(ExpectedDetails $required) use ($test) {
                $test->assertTrue(isset($required['resolution']));
                return new Details();
            }
        );
    }

    public function testBuildsRequirementsWithOptions()
    {
        $test = $this;

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
                                array('name' => 'Fixed'),
                                array('name' => 'Dupe')
                            )
                        ),
                        'stupidfield' => array('required' => false)
                    )
                )
            )))
        ;

        $workflow = new JiraWorkflow($repository);
        $transition = $workflow->buildTransition(
            new Number(1),
            new Status('closed'),
            function(ExpectedDetails $required) use ($test) {
                $test->assertEquals(array('Fixed', 'Dupe'), $required['resolution']->getOptions());
                return new Details();
            }
        );
    }
}
