<?php

namespace Qissues\Trackers\BitBucket;

use Qissues\Trackers\BitBucket\BitBucketMetadata;

class BitBucketMetadataTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorFailsWithoutComponents()
    {
        $this->setExpectedException('InvalidArgumentException', 'components');
        $metadata = new BitBucketMetadata(array());
    }

    public function testGetAllowedComponents()
    {
        $metadata = new BitBucketMetadata(array('components' => array(
            array('id' => 1, 'name' => 'good'),
            array('id' => 2, 'name' => 'bad')
        )));

        $allowed = $metadata->getAllowedComponents();

        $this->assertEquals(array('good', 'bad'), $allowed);
    }
}
