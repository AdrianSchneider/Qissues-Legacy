<?php

namespace Qissues\Domain\Model;

/**
 * Filters Issue instances using SearchCriteria
 */
class CriteriaFilter
{
    protected $criteria;

    /**
     * Bring criteria ito scope
     * @param SearchCriteria $criteria
     */
    public function __construct(SearchCriteria $criteria)
    {
        $this->criteria = $criteria;
    }

    /**
     * Filter callable
     *
     * @param Issue $issue
     * @return boolean (true to keep)
     */
    public function __invoke(/*Issue*/ $issue)
    {
        return true;

        if (!$this->satisfiesStatuses($issue)) {
            return false;
        }

        if (!$this->satisfiesLabels($issue)) {
            return false;
        }

        if (!$this->satisfiesAssignees($issue)) {
            return false;
        }

        if (!$this->satisfiesTypes($issue)) {
            return false;
        }

        if (!$this->satisfiesKeywords($issue)) {
            return false;
        }

        return true;
    }

    protected function satisfiesStatuses(Issue $issue)
    {
        if ($statuses = $this->criteria->getStatuses()) {
            foreach ($statuses as $status) {
                if (stripos($issue->getStatus()->getStatus(), $status->getStatus()) !== false) {
                    return true;
                }
            }
            return false;
        }

        return true;
    }

    protected function satisfiesLabels(Issue $issue)
    {
        if ($labels = $this->criteria->getLabels()) {
            foreach ($labels as $label) {
                foreach ($issue->getLabels() as $issueLabel) {
                    if (stripos($issueLabel->getName(), $label->getName()) !== false) {
                        return true;
                    }
                }
            }
            return false;
        }

        return true;
    }

    protected function satisfiesAssignees(Issue $issue)
    {
        if ($assignees = $this->criteria->getAssignees()) {
            foreach ($assignees as $assignee) {
                if (stripos($issue->getAssignee()->getAccount(), $assignee->getAccount()) !== false) {
                    return true;
                }
                if (stripos($issue->getAssignee()->getName(), $assignee->getAccount()) !== false) {
                    return true;
                }
            }
            return false;
        }

        return true;
    }

    protected function satisfiesTypes(Issue $issue)
    {
        if ($types = $this->criteria->getTypes()) {
            foreach ($types as $type) {
                if (stripos($issue->getType()->getName(), $type->getName()) !== false) {
                    return true;
                }
            }
            return false;
        }

        return true;
    }

    protected function satisfiesKeywords(Issue $issue)
    {
        if ($keywords = $this->criteria->getKeywords()) {
            if (stripos($issue->getTitle(), $keywords) !== false) {
                return true;
            }
            if (stripos($issue->getDescription(), $keywords) !== false) {
                return true;
            }

            return false;
        }

        return true;
    }
}
