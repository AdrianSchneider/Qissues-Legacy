<?php

namespace Qissues\Tests\Model\Serializer;

use Qissues\Model\Comment;
use Qissues\Model\Meta\User;
use Qissues\Model\Serializer\CommentSerializer;

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
