<?php

namespace Tests\Qissues\System\Storage;

use Qissues\System\Storage\LocalStorage;

class LocalStorageTest extends \PHPUnit_Framework_TestCase
{
    public function testExpandsTildesToHomeDirectory()
    {
        $filename = '~/.test';
        $info = posix_getpwuid(posix_getuid());

        $filesystem = $this->getMockBuilder('Qissues\System\Filesystem')->disableOriginalConstructor()->getMock();
        $filesystem
            ->expects($this->once())
            ->method('exists')
            ->with("$info[dir]/.test")
            ->will($this->returnValue(false))
        ;

        $storage = new LocalStorage($filesystem, $filename);
    }

    public function testCreatesFileIfNotExist()
    {
        $filename = './.config.file';

        $filesystem = $this->getMockBuilder('Qissues\System\Filesystem')->disableOriginalConstructor()->getMock();
        $filesystem
            ->expects($this->once())
            ->method('exists')
            ->will($this->returnValue(false))
        ;
        $filesystem
            ->expects($this->once())
            ->method('dumpFile')
            ->with($filename, '{}')
        ;

        $storage = new LocalStorage($filesystem, $filename);
    }

    public function testLoadsFileContentsIntoMemory()
    {
        $filename = './.config.file';

        $filesystem = $this->getMockBuilder('Qissues\System\Filesystem')->disableOriginalConstructor()->getMock();
        $filesystem
            ->expects($this->once())
            ->method('exists')
            ->will($this->returnValue(true))
        ;
        $filesystem
            ->expects($this->once())
            ->method('read')
            ->with($filename)
            ->will($this->returnValue(json_encode(array('a' => 'b'))))
        ;

        $storage = new LocalStorage($filesystem, $filename);
        $this->assertEquals('b', $storage->get('a'));
    }

    public function testCanSaveAndRetrieveInformation()
    {
        $filename = './.config.file';

        $filesystem = $this->getMockBuilder('Qissues\System\Filesystem')->disableOriginalConstructor()->getMock();
        $filesystem
            ->expects($this->once())
            ->method('exists')
            ->will($this->returnValue(true))
        ;
        $filesystem
            ->expects($this->once())
            ->method('read')
            ->with($filename)
            ->will($this->returnValue(json_encode(array())))
        ;
        $filesystem
            ->expects($this->once())
            ->method('dumpFile')
            ->with($filename, json_encode(array('name'=> 'adrian'), true))
        ;

        $storage = new LocalStorage($filesystem, $filename);
        $storage->set('name', 'adrian');

        $this->assertEquals('adrian', $storage->get('name'));
    }

    public function testExists()
    {
        $filename = './.config.file';

        $filesystem = $this->getMockBuilder('Qissues\System\Filesystem')->disableOriginalConstructor()->getMock();
        $filesystem
            ->expects($this->once())
            ->method('exists')
            ->will($this->returnValue(true))
        ;
        $filesystem
            ->expects($this->once())
            ->method('read')
            ->with($filename)
            ->will($this->returnValue(json_encode(array())))
        ;

        $storage = new LocalStorage($filesystem, $filename);
        $this->assertFalse($storage->exists('anything'));
    }

    public function testThrowsExceptionWhenAccessingInvalidKey()
    {
        $filename = './.config.file';

        $filesystem = $this->getMockBuilder('Qissues\System\Filesystem')->disableOriginalConstructor()->getMock();
        $filesystem
            ->expects($this->once())
            ->method('exists')
            ->will($this->returnValue(true))
        ;
        $filesystem
            ->expects($this->once())
            ->method('read')
            ->with($filename)
            ->will($this->returnValue(json_encode(array())))
        ;

        $this->setExpectedException('InvalidArgumentException');

        $storage = new LocalStorage($filesystem, $filename);
        $storage->get('anything');
    }
}
