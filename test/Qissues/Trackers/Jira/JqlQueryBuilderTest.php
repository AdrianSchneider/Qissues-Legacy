<?php

namespace Qissues\Trackers\Jira;

use Qissues\Model\Meta\User;
use Qissues\Model\Meta\Type;
use Qissues\Trackers\Jira\JqlQueryBuilder;
use Qissues\Model\Querying\SearchCriteria;

class JqlQueryBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testAlwaysIncludesProject()
    {
        $builder = $this->getBuilder(array('id' => 5));
        $jql = $builder->build(new SearchCriteria());
        $this->assertEquals("project = '5'", $jql);
    }

    public function testAssigneesAddWhereClause()
    {
        $criteria = new SearchCriteria();
        $criteria->addAssignee(new User('adrian'));
        $criteria->addAssignee(new User('jim'));

        $builder = $this->getBuilder(array('id' => 5));
        $jql = $builder->build($criteria);

        $this->assertContains("assignee IN ('adrian','jim')", $jql);
    }

    public function testTypesAddClause()
    {
        $criteria = new SearchCriteria();
        $criteria->addType(new Type('bug'));
        $criteria->addType(new Type('feature'));

        $builder = $this->getBuilder(array('id' => 5));
        $jql = $builder->build($criteria);

        $this->assertContains("issuetype IN ('bug','feature')", $jql);
    }

    protected function getBuilder(array $metadata = array())
    {
        return new JqlQueryBuilder(new JiraMetadata($metadata));
    }
}
