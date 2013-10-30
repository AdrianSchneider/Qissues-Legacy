<?php

namespace Qissues\Model;

use Qissues\Model\Meta\User;
use Qissues\System\DataType\ReadOnlyArrayAccess;

class Comment extends ReadOnlyArrayAccess
{
    protected $message;
    protected $author;
    protected $date;

    public function __construct($message, User $author, \DateTime $date)
    {
        $this->message = $message;
        $this->author = $author;
        $this->date = $date;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function getDate()
    {
        return $this->date;
    }
}
