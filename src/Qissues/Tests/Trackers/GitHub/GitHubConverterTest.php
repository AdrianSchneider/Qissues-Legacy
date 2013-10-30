<?php

namespace Qissues\Tests\Trackers\GitHub;

use Qissues\Model\Posting\NewIssue;
use Qissues\Trackers\GitHub\GitHubConverter;

class GitHubConverterTest extends \PHPUnit_Framework_TestCase
{
    public function testToIssueCreatesAnIssue()
    {
        $converter = new GitHubConverter();
        $issue = $converter->toIssue(array(
            'number' => 1,
            'title' => 'Hello World',
            'body' => 'Oh snap'
        ));

        $this->assertInstanceOf('Qissues\Model\Issue', $issue);
        $this->assertEquals(1, $issue->getId());
        $this->assertEquals('Hello World', $issue->getTitle());
        $this->assertEquals('Oh snap', $issue->getDescription());
    }

    public function testToNewIssueCreatesANewIssue()
    {
        $converter = new GitHubConverter();
        $issue = $converter->toNewIssue(array(
            'title' => 'Hello World',
            'description' => 'Oh snap'
        ));

        $this->assertInstanceOf('Qissues\Model\Posting\NewIssue', $issue);
        $this->assertEquals('Hello World', $issue->getTitle());
        $this->assertEquals('Oh snap', $issue->getDescription());
    }

    public function testIssueToArrayConverts()
    {
        $converter = new GitHubConverter();
        $issue = new NewIssue('Hello', 'World');
        $rawIssue = $converter->issueToArray($issue);

        $this->assertEquals('Hello', $rawIssue['title']);
        $this->assertEquals('World', $rawIssue['body']);
    }
}
