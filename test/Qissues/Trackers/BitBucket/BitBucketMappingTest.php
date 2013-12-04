<?php

namespace Qissues\Tests\Trackers\BitBucket;

use Qissues\Domain\Shared\User;
use Qissues\Domain\Shared\Status;
use Qissues\Domain\Shared\Priority;
use Qissues\Domain\Shared\Type;
use Qissues\Domain\Shared\Label;
use Qissues\Domain\Model\SearchCriteria;
use Qissues\Trackers\BitBucket\BitBucketMapping;

class BitBucketMappingTest extends \PHPUnit_Framework_TestCase
{
    public function testGetExpectedDetails()
    {
        $mapping = new BitBucketMapping();
        $details = $mapping->getExpectedDetails();
        foreach (array('title', 'description', 'assignee', 'type', 'label', 'priority') as $field) {
            $this->assertTrue(isset($details[$field]));
        }
    }

    public function testQueryFilterByType()
    {
        $criteria = new SearchCriteria();
        $criteria->addType(new Type('bug'));

        $mapping = new BitBucketMapping();
        $query = $mapping->buildSearchQuery($criteria);

        $this->assertEquals(array('bug'), $query['kind']);
    }

    public function testQueryFilterByUnsupportedTypeThrowsException()
    {
        $this->setExpectedException('DomainException', 'type');

        $criteria = new SearchCriteria();
        $criteria->addType(new Type('peanut'));

        $mapping = new BitBucketMapping();
        $mapping->buildSearchQuery($criteria);
    }

    public function testQueryFilterByStatuses()
    {
        $criteria = new SearchCriteria();
        $criteria->addStatus(new Status('resolved'));

        $mapping = new BitBucketMapping();
        $query = $mapping->buildSearchQuery($criteria);

        $this->assertEquals(array('resolved'), $query['status']);
    }

    public function testQueryFilterByUnsupportedStatusThrowsException()
    {
        $this->setExpectedException('DomainException', 'status');

        $criteria = new SearchCriteria();
        $criteria->addStatus(new Status('lame'));

        $mapping = new BitBucketMapping();
        $mapping->buildSearchQuery($criteria);
    }

    public function testQueryFilterByAssignees()
    {
        $criteria = new SearchCriteria();
        $criteria->addAssignee(new User('joe'));

        $mapping = new BitBucketMapping();
        $query = $mapping->buildSearchQuery($criteria);

        $this->assertEquals(array('joe'), $query['responsible']);
    }

    public function testQueryFilterByLabel()
    {
        $criteria = new SearchCriteria();
        $criteria->addLabel(new Label('cool'));

        $mapping = new BitBucketMapping();
        $query = $mapping->buildSearchQuery($criteria);

        $this->assertEquals(array('cool'), $query['component']);
    }

    public function testQueryFilterByKeywords()
    {
        $criteria = new SearchCriteria();
        $criteria->setKeywords('eggnog');

        $mapping = new BitBucketMapping();
        $query = $mapping->buildSearchQuery($criteria);

        $this->assertEquals('eggnog', $query['search']);
    }

    public function testQueryFilterByPriority()
    {
        $criteria = new SearchCriteria();
        $criteria->addPriority(new Priority(0, 'major'));

        $mapping = new BitBucketMapping();
        $query = $mapping->buildSearchQuery($criteria);

        $this->assertEquals(array('major'), $query['priority']);
    }

    public function testQueryFilterByNumericPriority()
    {
        $criteria = new SearchCriteria();
        $criteria->addPriority(new Priority(3, ''));

        $mapping = new BitBucketMapping();
        $query = $mapping->buildSearchQuery($criteria);

        $this->assertEquals(array('major'), $query['priority']);
    }

    public function testQueryFilterByUnsupportedPriorityThrowsException()
    {
        $this->setExpectedException('DomainException', 'priority');

        $criteria = new SearchCriteria();
        $criteria->addPriority(new Priority(99, 'made up'));

        $mapping = new BitBucketMapping();
        $mapping->buildSearchQuery($criteria);
    }

    public function testQueryFilterByNumbersThrowsException()
    {
        $this->setExpectedException('DomainException', 'numbers');

        $criteria = new SearchCriteria();
        $criteria->setNumbers(array(1, 2, 3));

        $mapping = new BitBucketMapping();
        $mapping->buildSearchQuery($criteria);
    }

    public function testQueryPagination()
    {
        $criteria = new SearchCriteria();
        $criteria->setPaging(3, 25);

        $mapping = new BitBucketMapping();
        $query = $mapping->buildSearchQuery($criteria);

        $this->assertEquals(25, $query['limit']);
        $this->assertEquals(50, $query['offset']);
    }

    public function testSorting()
    {
        // TODO
    }

    public function testGetStatusMatching()
    {
        $mapping = new BitBucketMapping();
        $status = $mapping->getStatusMatching(new Status('open'));
        $this->assertEquals('open', $status->getStatus());
    }

    public function testGetStatusFuzzyMatching()
    {
        $mapping = new BitBucketMapping();
        $status = $mapping->getStatusMatching(new Status('hold'));
        $this->assertEquals('on hold', $status->getStatus());
    }

    public function testGetStatusThrowsException()
    {
        $this->setExpectedException('DomainException', 'invalid');
        $mapping = new BitBucketMapping();
        $mapping->getStatusMatching(new Status('pizza'));
    }
}
