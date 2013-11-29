<?php

namespace Qissues\Interfaces\Console\Output\Issue;

use Qissues\Domain\Model\Issue;
use Qissues\Domain\Model\Comment;
use Qissues\Interfaces\Console\Output\Serializer\IssueSerializer;
use Qissues\Interfaces\Console\Output\Serializer\CommentSerializer;

class JsonView
{
    protected $issueSerializer;
    protected $commentSerializer;

    public function __construct(IssueSerializer $issueSerializer, CommentSerializer $commentSerializer)
    {
        $this->issueSerializer = $issueSerializer;
        $this->commentSerializer = $commentSerializer;

    }

    /**
     * Render an Issue as JSON
     *
     * @param Issue $issue
     * @param integer $width
     * @param integer $height
     * @param array $comments
     */
    public function render(Issue $issue, $width, $height, array $comments)
    {
        return json_encode(array_merge(
            $this->issueSerializer->serialize($issue),
            array('comments' => array_map(array($this->commentSerializer, 'serialize'), $comments))
        ));
    }
}
