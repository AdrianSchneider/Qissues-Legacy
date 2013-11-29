<?php

namespace Qissues\Tests\Console\Input;

use Qissues\Interfaces\Console\Input\GitId;

class GitIdTest extends \PHPUnit_Framework_TestCase
{
    public function testUseIdFromInputFirst()
    {
        $input = $this->getMock('Symfony\Component\Console\Input\InputInterface');
        $input
            ->expects($this->once())
            ->method('getArgument')
            ->with('issue')
            ->will($this->returnValue(5))
        ;

        $plugin = new GitId($this->getMock('Qissues\System\Shell\Shell'));
        $this->assertEquals(5, $plugin->getId($input));
    }

    public function testGetIdFromBranch()
    {
        $shell = $this->getMock('Qissues\System\Shell\Shell');
        $shell
            ->expects($this->once())
            ->method('run')
            ->will($this->returnValue("asdf-5\n"))
        ;

        $input = $this->getMock('Symfony\Component\Console\Input\InputInterface');
        $input
            ->expects($this->once())
            ->method('getArgument')
            ->with('issue')
            ->will($this->returnValue(null))
        ;

        $plugin = new GitId($shell);
        $this->assertEquals(5, $plugin->getId($input));
    }

    public function testIgnoresWhenNoBranch()
    {
        $shell = $this->getMock('Qissues\System\Shell\Shell');
        $shell
            ->expects($this->once())
            ->method('run')
            ->will($this->returnValue("master\n"))
        ;

        $input = $this->getMock('Symfony\Component\Console\Input\InputInterface');
        $input
            ->expects($this->once())
            ->method('getArgument')
            ->with('issue')
            ->will($this->returnValue(null))
        ;

        $plugin = new GitId($shell);
        $this->assertNull($plugin->getId($input));
    }
}
