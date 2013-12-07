<?php

namespace Qissues\Domain\Model;

use Qissues\Domain\Model\CriteriaSorter;
use Qissues\Domain\Shared\Status;

class CriteriaSorterTest extends \PHPUnit_Framework_TestCase
{
    public function testSortByTitle()
    {
        $criteria = new SearchCriteria();
        $criteria->addSortField('title');

        $sorter = new CriteriaSorter($criteria);
        $score = $sorter(
            new Issue(2, 'zzz', 'd', new Status('open'), new \DateTime, new \DateTime),
            new Issue(1, 'aaa', 'd', new Status('open'), new \DateTime, new \DateTime)
        );

        $this->assertEquals(1, $score);
    }

    public function testSortsByMultipleFields()
    {
        $criteria = new SearchCriteria();
        $criteria->addSortField('title');
        $criteria->addSortField('description');

        $sorter = new CriteriaSorter($criteria);
        $score = $sorter(
            new Issue(1, 'aaa', 'xxx', new Status('open'), new \DateTime, new \DateTime),
            new Issue(2, 'aaa', 'zzz', new Status('open'), new \DateTime, new \DateTime)
        );

        $this->assertEquals(-1, $score);
    }

    public function testMultiSortProcess()
    {
        $criteria = new SearchCriteria();
        $criteria->addSortField('title');
        $criteria->addSortField('description');

        $issues = array(
            new Issue(1, 'bbb', 'aaa', new Status('open'), new \DateTime, new \DateTime),
            new Issue(2, 'aaa', 'bbb', new Status('open'), new \DateTime, new \DateTime),
            new Issue(3, 'aaa', 'aaa', new Status('open'), new \DateTime, new \DateTime),
        );

        usort($issues, new CriteriaSorter($criteria));

        $ids = array_map(function($i) { return $i->getId(); }, $issues);
        $this->assertEquals(array(3, 2, 1), $ids);
    }
}
