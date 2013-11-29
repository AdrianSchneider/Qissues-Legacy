<?php

namespace Qissues\Model\Services;

use Qissues\Model\Querying\Number;
use Qissues\Model\Posting\NewComment;
use Qissues\Model\Tracker\IssueRepository;

class CommentOnIssue
{
    protected $repository;

    public function __construct(IssueRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Posts a comment to an issue
     *
     * @param NewComment $comment
     * @param Number $issue
     */
    public function __invoke(NewComent $comment, Number $issue)
    {
        $this->repository->comment($number, $comment);
    }
}
