<?php

namespace Qissues\Domain\Service;

use Qissues\Domain\Model\Number;
use Qissues\Domain\Model\NewComment;
use Qissues\Domain\Model\IssueRepository;

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
