<?php

namespace Qissues\Trackers\Jira;

use Qissues\Domain\Model\Number;
use Qissues\Domain\Model\Workflow;
use Qissues\Domain\Model\Transition;
use Qissues\Domain\Shared\Details;
use Qissues\Domain\Shared\ExpectedDetail;
use Qissues\Domain\Shared\ExpectedDetails;
use Qissues\Domain\Shared\Status;
use Qissues\Domain\Model\Exception\MappingException;

/**
 * Support JIRA's dynamic workflow process
 *
 * Each issue type per project per credentials has a series
 * of transitions (to statuses) available.
 *
 * Transitions don't appear to be cachable (ids) for some reason,
 * so it is calculated at run-time.
 */
class JiraWorkflow implements Workflow
{
    protected $repository;

    /**
     * @param JiraRepository $repository
     */
    public function __construct(JiraRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Builds a transition, querying JIRA at run-time
     *
     * {@inheritDoc}
     */
    public function buildTransition(Number $issue, Status $status, /*Callable*/ $builder = null)
    {
        $requirements = $this->getRequirements($issue, $status);
        if (count($requirements)) {
            $details = call_user_func($builder, $requirements);
        } else {
            $details = new Details();
        }

        return new Transition($status, $details);
    }

    /**
     * Performs a status change on JIRA
     *
     * {@inheritDoc}
     */
    public function apply(Transition $transition, Number $issue)
    {
        $info = $this->getJiraTransition($issue, $transition->getStatus());

        $this->repository->changeStatus(
            $issue,
            $transition->getStatus(),
            $info['id'],
            $transition->getDetails()
        );
    }

    /**
     * Gets all required fields from JIRA
     *
     * {@inheritDoc}
     */
    protected function getRequirements(Number $issue, Status $status)
    {
        $info = $this->getJiraTransition($issue, $status);

        $expectations = array();
        foreach ($info['fields'] as $fieldName => $info) {
            if (empty($info['required'])) {
                continue;
            }

            $options = array();
            if (!empty($info['allowedValues'])) {
                foreach ($info['allowedValues'] as $value) {
                    $options[] = $value['name'];
                }
            }

            $expectations[] = new ExpectedDetail($fieldName, true, '', $options); 
        }

        return new ExpectedDetails($expectations);
    }

    /**
     * Queries the repository for the transition info
     * @param Number $issue
     * @param Status $status
     * @return array|null
     * @throws MappingException when invalid status
     */
    protected function getJiraTransition(Number $issue, Status $status)
    {
        $valid = array();

        foreach ($this->repository->lookupTransitions($issue) as $trans) {
            if (stripos($trans['to']['name'], $status->getStatus()) !== false) {
                return $trans;
            }

            $valid[] = $trans['to']['name'];
        }

        throw new MappingException("supported transitions: " . implode(', ', $valid));
    }
}
