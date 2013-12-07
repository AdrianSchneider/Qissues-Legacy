<?php

namespace Qissues\Domain\Model;

class CriteriaSorter
{
    protected $criteria;

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
        return 0;

        /*
            foreach (array_reverse($this->criteria->getSortFields()) as $field => $value) {

            }
        */
    }
}
