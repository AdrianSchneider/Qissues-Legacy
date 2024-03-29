<?php

namespace Qissues\Domain\Model;

use Qissues\Domain\Shared\Priority;

class CriteriaSorter
{
    protected $criteria;

    protected $fieldTranslations = array(
        'created' => 'dateCreated',
        'updated' => 'dateUpdated'
    );

    protected $fieldMapping = array(
        'title'       => 'compareStrings',
        'description' => 'compareStrings',
        'created'     => 'compareDates',
        'updated'     => 'compareDates',
        'dateCreated' => 'compareDates',
        'dateUpdated' => 'compareDates',
        'priority'    => 'comparePriorities'
    );

    /**
     * @param SearchCriteria $criteria
     */
    public function __construct(SearchCriteria $criteria)
    {
        $this->criteria = $criteria;
    }

    /**
     * Sort the issues against each other
     *
     * Multi-sort based on search criteria
     * To handle the recursive 0 checking on matches, the search
     * starts in reverse order, storing the values for the given pair
     *
     * @param Issue $a
     * @param Issue $b
     * @return integer -1,0,1 sort score of a against b
     */
    public function __invoke(Issue $a, Issue $b)
    {
        foreach ($this->getSortValues($a, $b) as $score) {
            if ($score) {
                return $score;
            }
        }

        return 0;
    }

    /**
     * Creates a list of scores
     *
     * @param Issue $issue
     * @param Issue $issue
     * @return array
     */
    protected function getSortValues(Issue $a, Issue $b)
    {
        $out = array();
        foreach ($this->criteria->getSortFields() as $field) {
            if (isset($this->fieldTranslations[$field])) {
                $field = $this->fieldTranslations[$field];
            }

            if (!isset($this->fieldMapping[$field])) {
                $fields = implode(', ', array_keys($this->fieldMapping));
                throw new \DomainException("$field is an invalid sort field; valid fields: $fields");
            }

            $comparison = $this->fieldMapping[$field];
            $getter = 'get' . ucfirst($field);

            $out[] = call_user_func(array($this, $comparison), $a->$getter(), $b->$getter());
        }

        return $out;
    }

    /**
     * Wraps strcmp to limit results within -1 and 1
     *
     * @param string $a
     * @param string $b
     * @return integer -1, 0, 1
     */
    protected function compareStrings($a, $b)
    {
        return max(-1, min(1, strcmp(strtolower($a), strtolower($b))));
    }

    /**
     * Sort comparison on dates
     *
     * @param DateTime $a
     * @param DateTime $b
     * @return integer -1, 0, 1
     */
    protected function compareDates(\DateTime $a, \DateTime $b)
    {
        return $a == $b ? 0 : ($a < $b ? -1 : 1);
    }

    /**
     * Sort comparison on Priorities (DESC)
     *
     * @param Priority $a
     * @param Priority $b
     * @return integer -1, 0, 1
     */
    protected function comparePriorities(Priority $a = null, Priority $b = null)
    {
        $a = $a ? $a->getPriority() : 0;
        $b = $b ? $b->getPriority() : 0;

        return $a == $b ? 0 : ($a > $b ? -1 : 1);
    }
}
