<?php

namespace Qissues\Domain\Serializer;

use Qissues\Domain\Comment;

class CommentSerializer
{
    /**
     * Serializes a Comment
     * @param Comment $comment
     * @return array flat representation
     */
    public function serialize(Comment $comment)
    {
        return array(
            'message' => $comment->getMessage(),
            'author' => $comment->getAuthor()->getAccount(),
            'date' => $comment->getDate()
        );
    }
}
