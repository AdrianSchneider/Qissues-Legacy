<?php

namespace Qissues\Domain\Tracker\Metadata;

class NullMetadata implements Metadata
{
    public function __call($method, $args)
    {
        throw new NullMetadataException();
    }
}
