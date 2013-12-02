<?php

namespace Qissues\Interfaces\Console\Input\FileFormats;

use Qissues\Domain\Shared\Details;
use Qissues\Domain\Shared\ExpectedDetails;

class JsonFormat implements FileFormat
{
    /**
     * {@inheritDoc}
     */
    public function seed(ExpectedDetails $expectations)
    {
        $flags = 0;
        if (defined('JSON_PRETTY_PRINT')) {
            $flags |= JSON_PRETTY_PRINT;
        }

        return json_encode($expectations->getDefaults(), $flags);
    }

    /**
     * {@inheritDoc}
     */
    public function parse($content)
    {
        return new Details(json_decode($content, true));
    }
}
