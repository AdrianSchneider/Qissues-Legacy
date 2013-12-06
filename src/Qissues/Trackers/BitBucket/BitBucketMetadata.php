<?php

namespace Qissues\Trackers\BitBucket;

use Qissues\Application\Tracker\Metadata\Metadata;

class BitBucketMetadata implements Metadata
{
    protected $components;

    public function __construct(array $data)
    {
        if (!isset($data['components'])) {
            throw new \InvalidArgumentException('components are missing from metadata');
        }

        $this->components = $data['components'];
    }

    public function getAllowedComponents()
    {
        foreach ($this->components as $component) {
            $components[] = $component['name'];
        }
        return $components;
    }
}
