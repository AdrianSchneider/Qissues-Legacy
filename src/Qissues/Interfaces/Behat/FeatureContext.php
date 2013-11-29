<?php

use Qissues\Domain\Model\Number;
use Qissues\Domain\Model\SearchCriteria;
use Qissues\Domain\Shared\Status;
use Qissues\Domain\Shared\User;
use Qissues\Domain\Shared\Type;
use Qissues\Domain\Model\NewIssue;
use Qissues\Domain\Model\NewComment;
use Qissues\Trackers\InMemory\InMemoryRepository;
use Behat\Behat\Context\ClosuredContextInterface;
use Behat\Behat\Context\TranslatedContextInterface;
use Behat\Behat\Context\BehatContext;
use Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

require_once 'PHPUnit/Autoload.php';
require_once 'PHPUnit/Framework/Assert/Functions.php';

/**
 * Features context.
 */
class FeatureContext extends BehatContext
{
    protected $repository;
    protected $lastResponse;

    /**
     * @beforeScenario
     */
    public function startNewRepository()
    {
        $this->repository = new InMemoryRepository();
    }

    /**
     * @Given /^the following issues:$/
     */
    public function theFollowingIssues(TableNode $table)
    {
        $this->repository = new InMemoryRepository($table->getHash());
    }

    /**
     * @When /^I assign issue "([^"]*)" to "([^"]*)"$/
     */
    public function iAssignIssueTo($num, $assignee)
    {
        $service = new \Qissues\Domain\Service\AssignIssue($this->repository);
        $this->lastResponse = $service(new User($assignee), new Number($num));
    }

    /**
     * @Then /^Issue number "([^"]*)" should be assigned to "([^"]*)"$/
     */
    public function issueNumberShouldBeAssignedTo($num, $assignee)
    {
        assertEquals(
            $assignee,
            $this->getIssue($num)->getAssignee()->getAccount()
        );
    }

    /**
     * @When /^I query issues$/
     */
    public function iQueryIssues()
    {
        $service = new \Qissues\Domain\Service\QueryIssues($this->repository);
        $this->lastResponse = $service(new SearchCriteria());
    }

    /**
     * @Then /^I should get "([^"]*)" results$/
     */
    public function iShouldGetResults($amount)
    {
        assertCount((int)$amount, $this->lastResponse);
    }

    /**
     * @When /^I create an issue "([^"]*)" described as "([^"]*)"$/
     */
    public function iCreateAnIssue($title, $description)
    {
        $service = new \Qissues\Domain\Service\CreateIssue($this->repository);
        $this->lastResponse = $service(new NewIssue($title, $description));
    }

    /**
     * @When /^I delete issue number "([^"]*)"$/
     */
    public function iDeleteAnIssue($number)
    {
        $service = new \Qissues\Domain\Service\DeleteIssue($this->repository);
        $this->lastResponse = $service(new Number($number));
    }

    /**
     * @Then /^I should get the number "([^"]*)" back$/
     */
    public function iShouldGetTheCreatedNumberBack($number)
    {
        assertInstanceOf('Qissues\Domain\Model\Number', $this->lastResponse);
        assertEquals($number, (string)$this->lastResponse);
    }

    /**
     * @Then /^Issue number "([^"]*)" should be titled "([^"]*)"$/
     */
    public function issueShouldBeTitled($num, $title)
    {
        assertEquals($title, $this->getIssue($num)->getTitle());
    }

    /**
     * @Then /^Issue number "([^"]*)" should be deleted$/
     */
    public function issueShouldBeDeleted($num)
    {
        assertNull($this->getIssue($num));
    }

    /**
     * Shortcut to grab an issue
     *
     * @param integer $num
     * @return Issue
     */
    protected function getIssue($num)
    {
        return $this->repository->lookup(new Number($num));
    }
}
