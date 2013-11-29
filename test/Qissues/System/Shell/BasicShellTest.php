<?php

namespace Qissues\Tests\System\Shell;

use Qissues\System\Shell\BasicShell;

class BasicShellTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnsOutput()
    {
        $shell = new BasicShell();
        $this->assertEquals('hello world', $shell->run("echo 'hello world'"));
    }

    public function testEscapesOutput()
    {
        $shell = new BasicShell();
        $this->assertEquals("'hello world'", $shell->escape('hello world'));
    }
}
