<?php

namespace Qissues\Trackers\Shared;

interface MetadataStorage
{
    function exists($key);
    function get($key);
    function set($key, $value);
}
