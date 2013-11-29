<?php

namespace Qissues\Domain\Service;

use Qissues\Domain\Model\Number;
use Qissues\Domain\Model\Message;
use Qissues\Domain\Model\Request\NewComment;
use Qissues\Domain\Service\CommentOnIssue;

class CommentOnIssueTest extends \PHPUnit_Framework_TestCase
{
    public function testComment()
    {
        $comment = new NewComment(
            $issue = new Number(1),
            $message = new Message('hello world')
        );

        $repository = $this->getMock('Qissues\Domain\Model\IssueRepository');
        $repository
            ->expects($this->once())
            ->method('comment')
            ->with($issue, $message)
        ;

        $service = new CommentOnIssue($repository);
        $service($comment);
    }
}
