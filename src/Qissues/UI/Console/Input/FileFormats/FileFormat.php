<?php

namespace Qissues\Console\Input\FileFormats;

interface FileFormat
{
    /**
     * Prepare contents to seed a file with in order to parse later
     * @param array $fields
     * @return string file contents
     */
    function seed(array $fields);

    /**
     * Extract fields from $input
     * @param string $input (user populated seed content)
     * @return array fields
     */
    function parse($input);
}
