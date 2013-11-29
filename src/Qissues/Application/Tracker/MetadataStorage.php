<?php

namespace Qissues\Application\Tracker;

interface MetadataStorage
{
    function exists($key);
    function get($key);
    function set($key, $value);
}
