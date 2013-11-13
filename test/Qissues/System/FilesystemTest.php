<?php

namespace Qissues\Tests\System;

use Qissues\System\Filesystem;

class FilesystemTest extends \PHPUnit_Framework_TestCase
{
    public function testReadsFromFilesystem()
    {
        $fs = new Filesystem();
        $this->assertEquals(file_get_contents(__FILE__), $fs->read(__FILE__));
    }
}
