<?php

namespace Qissues\Format;

use Qissues\Model\NewComment;

interface CommentConverter
{
    function toComment(array $comment);
    function toNewComment(array $comment);
    function commentToArray(NewComment $comment);
}
