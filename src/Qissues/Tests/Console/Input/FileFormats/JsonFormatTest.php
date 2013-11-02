<?php

namespace Qissues\Tests\Console\Input\FileFormats;

use Qissues\Console\Input\FileFormats\JsonFormat;

class JsonFormatTest extends \PHPUnit_Framework_TestCase
{
    public function testSeedJustEncodes()
    {
        $payload = array('a' => 'b');
        $format = new JsonFormat();
        $formatted = $format->seed($payload);

        $this->assertEquals(json_encode($payload), $formatted);
    }

    public function testParseJustDecodes()
    {
        $payload = array('a' => 'b');
        $format = new JsonFormat();
        $parsed = $format->parse(json_encode($payload));

        $this->assertEquals($payload, $parsed);
    }
}
