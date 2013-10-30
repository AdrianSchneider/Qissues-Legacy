<?php

namespace Qissues\Format;

interface CommentConverter
{
    function toComment(array $comment);
    function toNewComment(array $comment);
    function commentToArray(NewComment $comment);
}
