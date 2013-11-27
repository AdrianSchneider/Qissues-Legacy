<?php

namespace Qissues\Trackers\Jira;

use Qissues\Model\Meta\Field;
use Qissues\Model\Querying\Number;
use Qissues\Model\Workflow\Workflow;
use Qissues\Model\Workflow\Transition;
use Qissues\Model\Workflow\TransitionDetails;
use Qissues\Model\Workflow\TransitionRequirements;
use Qissues\Model\Workflow\UnsupportedTransitionException;

class JiraWorkflow implements Workflow
{
    protected $repository;

    public function __construct(JiraRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Performs a status change on JIRA
     *
     * {@inheritDoc}
     */
    public function apply(Transition $transition, TransitionDetails $details)
    {
        $info = $this->getJiraTransition($transition);

        $this->repository->changeStatus(
            new Number($transition->getIssue()->getId()),
            $transition->getStatus(),
            $info['id'],
            $details->getDetails()
        );
    }

    /**
     * Gets all required fields from JIRA
     *
     * {@inheritDoc}
     */
    public function getRequirements(Transition $transition)
    {
        $info = $this->getJiraTransition($transition);

        $fields = array();
        foreach ($info['fields'] as $fieldName => $info) {
            $options = array();
            if (!empty($info['allowedValues'])) {
                foreach ($info['allowedValues'] as $value) {
                    $options[] = $value['name'];
                }
            }

            if ($info['required']) {
                $fields[] = new Field($fieldName, $options ? $options[0] : null, $options);
            }
        }

        return new TransitionRequirements($fields);
    }

    /**
     * Queries the repository for the transition info
     * @param Transition $transition
     * @return array|null
     */
    protected function getJiraTransition(Transition $transition)
    {
        $issue = $transition->getIssue();
        $status = $transition->getStatus();
        $number = new Number($issue->getId());

        $valid = array();

        foreach ($this->repository->lookupTransitions($number) as $trans) {
            if (stripos($trans['to']['name'], $status->getStatus()) !== false) {
                return $trans;
            }

            $valid[] = $trans['to']['name'];
        }

        throw new UnsupportedTransitionException("supported transitions: " . implode(', ', $valid));
    }
}
