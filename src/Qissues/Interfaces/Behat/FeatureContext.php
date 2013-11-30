<?php

use Qissues\Domain\Model\Number;
use Qissues\Domain\Model\Message;
use Qissues\Domain\Model\Transition;
use Qissues\Domain\Model\SearchCriteria;
use Qissues\Domain\Model\Request\NewIssue;
use Qissues\Domain\Model\Request\NewComment;
use Qissues\Domain\Model\Request\IssueAssignment;
use Qissues\Domain\Model\Request\IssueChanges;
use Qissues\Domain\Model\Request\IssueTransition;
use Qissues\Domain\Shared\Status;
use Qissues\Domain\Shared\Details;
use Qissues\Domain\Shared\User;
use Qissues\Domain\Shared\Type;
use Qissues\Trackers\InMemory\InMemoryRepository;
use Qissues\Trackers\Shared\BasicWorkflow;
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
        $this->workflow = new BasicWorkflow($this->repository);
    }

    /**
     * @Given /^the following issues:$/
     */
    public function theFollowingIssues(TableNode $table)
    {
        $this->repository = new InMemoryRepository($table->getHash());
        $this->workflow = new BasicWorkflow($this->repository);
    }

    /**
     * @When /^I lookup issue number "([^"]*)"$/
     */
    public function iLookupIssueNumber($number)
    {
        $service = new \Qissues\Domain\Service\LookupIssue($this->repository);
        $this->lastResponse = $service(new Number($number));
    }

    /**
     * @Then /^I should see an issue containing:$/
     */
    public function iShouldSeeAnIssueContaining(TableNode $assertions)
    {
        $issue = $this->lastResponse;
        foreach ($assertions->getRowsHash() as $field => $expected) {
            assertEquals($expected, call_user_func(array($issue, 'get' . ucfirst($field))));
        }
    }

    /**
     * @When /^I update issue number "([^"]*)" with:$/
     */
    public function iUpdateIssueNumberWith($number, TableNode $changes)
    {
        $changes = $changes->getHash();
        $service = new \Qissues\Domain\Service\EditIssue($this->repository);
        $service(new IssueChanges(
            new Number($number),
            $this->repository->getMapping()->toNewIssue($changes[0])
        ));
    }

    /**
     * @When /^I assign issue number "([^"]*)" to "([^"]*)"$/
     */
    public function iAssignIssueTo($num, $assignee)
    {
        $service = new \Qissues\Domain\Service\AssignIssue($this->repository);
        $this->lastResponse = $service(new IssueAssignment(
            new Number($num),
            new User($assignee)
        ));
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
     * @Then /^issue number "([^"]*)" should be assigned to "([^"]*)"$/
     */
    public function issueNumberShouldBeAssignedTo2($num, $assignee)
    {
        assertEquals(
            $assignee,
            $this->getIssue($num)->getAssignee()->getAccount()
        );
    }

    /**
     * @When /^I leave the comment "([^"]*)" on issue number "([^"]*)"$/
     */
    public function iLeaveTheCommentOnIssueNumber($message, $number)
    {
        $service = new \Qissues\Domain\Service\CommentOnIssue($this->repository);
        $this->lastResponse = $service(new NewComment(
            new Number($number),
            new Message($message)
        ));
    }

    /**
     * @Then /^issue number "([^"]*)" should have "([^"]*)" comment$/
     * @Then /^issue number "([^"]*)" should have "([^"]*)" comments$/
     */
    public function issueNumberShouldHaveComments($number, $comments)
    {
        assertEquals(
            $comments,
            $this->getIssue($number)->getCommentCount()
        );
    }

    /**
     * @When /^I transition issue number "([^"]*)" to "([^"]*)"$/
     */
    public function iTransitionIssueNumberTo($number, $status)
    {
        $service = new \Qissues\Domain\Service\TransitionIssue($this->workflow, $this->repository);
        $service(new IssueTransition(
            new Number($number),
            new Transition(new Status($status), new Details)
        ));
    }

    /**
     * @When /^I transition issue number "([^"]*)" to "([^"]*)" noting "([^"]*)"$/
     */
    public function iTransitionIssueNumberToNoting($number, $status, $message)
    {
        $service = new \Qissues\Domain\Service\TransitionIssue($this->workflow, $this->repository);
        $service(new IssueTransition(
            new Number($number),
            new Transition(new Status($status), new Details),
            new Message($message)
        ));
    }

    /**
     * @When /^I transition issue number "([^"]*)" to "([^"]*)" with:$/
     */
    public function iTransitionIssueNumberToWith($number, $status, TableNode $details)
    {
        $service = new \Qissues\Domain\Service\TransitionIssue($this->workflow, $this->repository);
        $service(new IssueTransition(
            new Number($number),
            new Transition(new Status($status), new Details($details->getRowsHash()))
        ));
    }

    /**
     * @Then /^issue number "([^"]*)" should be "([^"]*)"$/
     */
    public function issueNumberShouldBe($number, $status)
    {
        assertEquals(
            $status,
            $this->getIssue($number)->getStatus()->getStatus()
        );
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
