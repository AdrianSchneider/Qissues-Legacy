<?php

namespace Qissues\Domain\Tracker;

interface MetadataStorage
{
    function exists($key);
    function get($key);
    function set($key, $value);
}
