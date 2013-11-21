<?php

namespace Qissues\Model\Tracker\Metadata;

class NullMetadata implements Metadata
{
    public function __call($method, $args)
    {
        throw new NullMetadataException();
    }
}
