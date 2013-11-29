<?php

namespace Qissues\Console\Output\Issue;

use Qissues\Model\Issue;
use Qissues\Model\Comment;
use Qissues\Model\Serializer\IssueSerializer;
use Qissues\Model\Serializer\CommentSerializer;

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
