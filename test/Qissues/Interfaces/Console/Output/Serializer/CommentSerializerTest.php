<?php

namespace Qissues\Tests\Model\Serializer;

use Qissues\Domain\Model\Comment;
use Qissues\Domain\Shared\User;
use Qissues\Interfaces\Console\Output\Serializer\CommentSerializer;

class CommentSerializerTest extends \PHPUnit_Framework_TestCase
{
    public function testConvertComment()
    {
        $comment = new Comment('msg', new User('joe'), new \DateTime('now'));

        $serializer = new CommentSerializer();
        $serialized = $serializer->serialize($comment);

        $this->assertEquals(array(
            'message' => 'msg',
            'author' => 'joe',
            'date' => new \DateTime('now')
        ), $serialized);
    }
}
