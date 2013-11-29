<?php

namespace Qissues\Tests\Console\Input\FileFormats;

use Qissues\Interfaces\Console\Input\FileFormats\JsonFormat;

class JsonFormatTest extends \PHPUnit_Framework_TestCase
{
    public function testSeedJustEncodes()
    {
        $payload = array('a' => 'b');
        $format = new JsonFormat();
        $formatted = $format->seed($payload);

        $this->assertEquals(json_decode(json_encode($payload)), json_decode($formatted));
    }

    public function testParseJustDecodes()
    {
        $payload = array('a' => 'b');
        $format = new JsonFormat();
        $parsed = $format->parse(json_encode($payload));

        $this->assertEquals($payload, $parsed);
    }
}
