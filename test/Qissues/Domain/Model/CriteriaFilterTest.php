<?php

namespace Qissues\Domain\Model;

use Qissues\Domain\Model\Issue;
use Qissues\Domain\Model\CriteriaFilter;
use Qissues\Domain\Model\SearchCriteria;
use Qissues\Domain\Shared\Status;
use Qissues\Domain\Shared\User;
use Qissues\Domain\Shared\Type;
use Qissues\Domain\Shared\Label;

class CriteriaFilterTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptyCriteriaPasses()
    {
        $issue = new Issue(1, 't', 'd', new Status('open'), new \DateTime, new \DateTime);
        $filter = new CriteriaFilter(new SearchCriteria());
        $this->assertTrue($filter($issue));
    }

    public function testReturnsTrueIfOneStatusMatches()
    {
        $issue = new Issue(1, 't', 'd', new Status('open'), new \DateTime, new \DateTime);

        $criteria = new SearchCriteria();
        $criteria->addStatus(new Status('open'));

        $filter = new CriteriaFilter($criteria);
        $this->assertTrue($filter($issue));
    }

    public function testReturnsFalseIfNoStatusesMatch()
    {
        $issue = new Issue(1, 't', 'd', new Status('open'), new \DateTime, new \DateTime);

        $criteria = new SearchCriteria();
        $criteria->addStatus(new Status('closed'));

        $filter = new CriteriaFilter($criteria);
        $this->assertFalse($filter($issue));
    }

    public function testReturnsTrueIfOneLabelMatches()
    {
        $label = new Label('programming');
        $issue = new Issue(1, 't', 'd', new Status('open'), new \DateTime, new \DateTime, null, null, null, array($label));

        $criteria = new SearchCriteria();
        $criteria->addLabel(new Label('pro'));

        $filter = new CriteriaFilter($criteria);
        $this->assertTrue($filter($issue));
    }

    public function testReturnsFalseIfNoLabelsMatch()
    {
        $label = new Label('programming');
        $issue = new Issue(1, 't', 'd', new Status('open'), new \DateTime, new \DateTime, null, null, null, array($label));

        $criteria = new SearchCriteria();
        $criteria->addLabel(new Label('wat'));

        $filter = new CriteriaFilter($criteria);
        $this->assertFalse($filter($issue));
    }

    public function testReturnsTrueIfOneAssigneeMatches()
    {
        $issue = new Issue(1, 't', 'd', new Status('open'), new \DateTime, new \DateTime, new User('adrian'));

        $criteria = new SearchCriteria();
        $criteria->addAssignee(new User('adr'));

        $filter = new CriteriaFilter($criteria);
        $this->assertTrue($filter($issue));
    }

    public function testReturnsTrueIfOneAssigneeFullNameMatches()
    {
        $issue = new Issue(1, 't', 'd', new Status('open'), new \DateTime, new \DateTime, new User('adrian', 1, 'bilbo baggins'));

        $criteria = new SearchCriteria();
        $criteria->addAssignee(new User('baggins'));

        $filter = new CriteriaFilter($criteria);
        $this->assertTrue($filter($issue));
    }

    public function testReturnsFalseIfNoAssigneesMatch()
    {
        $issue = new Issue(1, 't', 'd', new Status('open'), new \DateTime, new \DateTime, new User('joe'));

        $criteria = new SearchCriteria();
        $criteria->addAssignee(new User('adrian'));

        $filter = new CriteriaFilter($criteria);
        $this->assertFalse($filter($issue));
    }

    public function testReturnsTrueIfOneTypeMatches()
    {
        $issue = new Issue(1, 't', 'd', new Status('open'), new \DateTime, new \DateTime, null, null, new Type('bug'));

        $criteria = new SearchCriteria();
        $criteria->addType(new Type('bug'));

        $filter = new CriteriaFilter($criteria);
        $this->assertTrue($filter($issue));
    }

    public function testReturnsFalseIfNoTypesMatch()
    {
        $issue = new Issue(1, 't', 'd', new Status('open'), new \DateTime, new \DateTime, null, null, new Type('bug'));

        $criteria = new SearchCriteria();
        $criteria->addType(new Type('feature'));

        $filter = new CriteriaFilter($criteria);
        $this->assertFalse($filter($issue));
    }

    public function testReturnsTrueIfKeywordsMatchesTitle()
    {
        $issue = new Issue(1, 'eggnog', 'd', new Status('open'), new \DateTime, new \DateTime);

        $criteria = new SearchCriteria();
        $criteria->setKeywords('eggnog');

        $filter = new CriteriaFilter($criteria);
        $this->assertTrue($filter($issue));
    }

    public function testReturnsTrueIfKeywordsMatchesDescription()
    {
        $issue = new Issue(1, 't', 'eggnog is delicious', new Status('open'), new \DateTime, new \DateTime);

        $criteria = new SearchCriteria();
        $criteria->setKeywords('eggnog');

        $filter = new CriteriaFilter($criteria);
        $this->assertTrue($filter($issue));
    }

    public function testReturnsFalseIfNoKeywordsMatch()
    {
        $issue = new Issue(1, 't', 'd', new Status('open'), new \DateTime, new \DateTime);

        $criteria = new SearchCriteria();
        $criteria->setKeywords('eggnog');

        $filter = new CriteriaFilter($criteria);
        $this->assertFalse($filter($issue));
    }
}
