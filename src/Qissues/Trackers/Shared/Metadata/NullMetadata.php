<?php

namespace Qissues\Trackers\Shared\Metadata;

class NullMetadata implements Metadata
{
    public function __call($method, $args)
    {
        throw new NullMetadataException();
    }
}
