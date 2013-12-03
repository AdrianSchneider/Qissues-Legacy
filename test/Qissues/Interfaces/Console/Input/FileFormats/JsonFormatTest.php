<?php

namespace Qissues\Tests\Console\Input\FileFormats;

use Qissues\Domain\Shared\Details;
use Qissues\Domain\Shared\ExpectedDetail;
use Qissues\Domain\Shared\ExpectedDetails;
use Qissues\Interfaces\Console\Input\FileFormats\JsonFormat;

class JsonFormatTest extends \PHPUnit_Framework_TestCase
{
    public function testSeedJustEncodes()
    {
        $rawPayload = array('field' => 'value');
        $expectations = new ExpectedDetails(array(new ExpectedDetail('field', true, 'value')));

        $format = new JsonFormat();
        $formatted = $format->seed($expectations);

        $this->assertEquals(json_decode(json_encode($rawPayload)), json_decode($formatted));
    }

    public function testParseJustDecodes()
    {
        $payload = array('a' => 'b');
        $format = new JsonFormat();
        $parsed = $format->parse(json_encode($payload));

        $this->assertEquals($payload, $parsed->getDetails());
    }
}
