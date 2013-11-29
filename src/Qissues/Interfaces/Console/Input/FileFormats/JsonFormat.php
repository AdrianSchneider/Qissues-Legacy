<?php

namespace Qissues\Interfaces\Console\Input\FileFormats;

class JsonFormat implements FileFormat
{
    /**
     * {@inheritDoc}
     */
    public function seed(array $fields)
    {
        $flags = 0;
        if (defined('JSON_PRETTY_PRINT')) {
            $flags |= JSON_PRETTY_PRINT;
        }

        return json_encode($fields, $flags);
    }

    /**
     * {@inheritDoc}
     */
    public function parse($content)
    {
        return json_decode($content, true);
    }
}
