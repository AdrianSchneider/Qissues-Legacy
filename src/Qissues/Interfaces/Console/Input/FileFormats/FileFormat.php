<?php

namespace Qissues\Interfaces\Console\Input\FileFormats;

use Qissues\Domain\Shared\ExpectedDetails;

interface FileFormat
{
    /**
     * Prepare contents to seed a file with in order to parse later
     * @param array $fields
     * @return string file contents
     */
    function seed(ExpectedDetails $expectations);

    /**
     * Extract Details from $input
     * @param string $input (user populated seed content)
     * @return Details
     */
    function parse($input);
}
