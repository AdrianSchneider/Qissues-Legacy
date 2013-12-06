<?php

namespace Qissues\Trackers\Jira;

use Qissues\Domain\Shared\User;
use Qissues\Domain\Shared\CurrentUser;
use Qissues\Domain\Shared\Type;
use Qissues\Domain\Shared\Status;
use Qissues\Trackers\Jira\JqlQueryBuilder;
use Qissues\Domain\Model\Number;
use Qissues\Domain\Model\SearchCriteria;

class JqlQueryBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testAlwaysIncludesProject()
    {
        $builder = $this->getBuilder(array('id' => 5));
        $jql = $builder->build(new SearchCriteria());
        $this->assertEquals("project = '5'", $jql);
    }

    public function testFilterByStatuses()
    {
        $criteria = new SearchCriteria();
        $criteria->addStatus(new Status('open'));
        $criteria->addStatus(new Status('closed'));

        $builder = $this->getBuilder(array('id' => 5));
        $jql = $builder->build($criteria);

        $this->assertContains("status IN ('open','closed')", $jql);
    }

    public function testFilterByAssignees()
    {
        $criteria = new SearchCriteria();
        $criteria->addAssignee(new User('adrian'));
        $criteria->addAssignee(new User('jim'));

        $builder = $this->getBuilder(array('id' => 5));
        $jql = $builder->build($criteria);

        $this->assertContains("assignee IN ('adrian','jim')", $jql);
    }

    public function testFilterByCurrentUserAssignee()
    {
        $criteria = new SearchCriteria();
        $criteria->addAssignee(new User('adrian'));
        $criteria->addAssignee(new CurrentUser());

        $builder = $this->getBuilder(array('id' => 5), 'aaa');
        $jql = $builder->build($criteria);

        $this->assertContains("assignee IN ('adrian','aaa')", $jql);
    }

    public function testFilterByTypes()
    {
        $criteria = new SearchCriteria();
        $criteria->addType(new Type('bug'));
        $criteria->addType(new Type('feature'));

        $builder = $this->getBuilder(array('id' => 5));
        $jql = $builder->build($criteria);

        $this->assertContains("issuetype IN ('bug','feature')", $jql);
    }

    public function testFilterByKeywords()
    {
        $criteria = new SearchCriteria();
        $criteria->setKeywords("hello world");

        $builder = $this->getBuilder(array('id' => 5));
        $jql = $builder->build($criteria);

        $this->assertContains("text ~ 'hello world'", $jql);
    }

    public function testFilterByIds()
    {
        $criteria = new SearchCriteria();
        $criteria->setNumbers(array(new Number(1), new Number(2)));

        $builder = $this->getBuilder(array('id' => 5, 'key' => 'PRX'));
        $jql = $builder->build($criteria);

        $this->assertContains("id IN ('PRX-1','PRX-2')", $jql);
    }

    public function testSortsByExpectedDetails()
    {
        $criteria = new SearchCriteria();
        $criteria->addSortField('priority');

        $builder = $this->getBuilder(array('id' => 5));
        $jql = $builder->build($criteria);

        $this->assertContains("ORDER BY priority DESC", $jql);
    }

    public function testSortsByTranslatedFields()
    {
        $criteria = new SearchCriteria();
        $criteria->addSortField('updated');

        $builder = $this->getBuilder(array('id' => 5));
        $jql = $builder->build($criteria);

        $this->assertContains("ORDER BY updatedDate DESC", $jql);
    }

    public function testSortsByMultipleFields()
    {
        $criteria = new SearchCriteria();
        $criteria->addSortField('priority');
        $criteria->addSortField('updated');

        $builder = $this->getBuilder(array('id' => 5));
        $jql = $builder->build($criteria);

        $this->assertContains("ORDER BY priority DESC, updatedDate DESC", $jql);
    }

    public function testSortingByInvalidFieldThrowsException()
    {
        $criteria = new SearchCriteria();
        $criteria->addSortField('peanuts');

        $builder = $this->getBuilder(array('id' => 5));

        $this->setExpectedException('DomainException', 'peanuts');
        $builder->build($criteria);

    }

    protected function getBuilder(array $metadata = array(), $un = '')
    {
        return new JqlQueryBuilder(new JiraMetadata($metadata), $un);
    }
}
