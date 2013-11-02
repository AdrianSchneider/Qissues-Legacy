<?php

namespace Qissues\Console\Input\FileFormats;

class JsonFormat implements FileFormat
{
    /**
     * {@inheritDoc}
     */
    public function seed(array $fields)
    {
        return json_encode($fields);
    }

    /**
     * {@inheritDoc}
     */
    public function parse($content)
    {
        return json_decode($content, true);
    }
}
