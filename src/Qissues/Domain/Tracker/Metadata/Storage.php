<?php

namespace Qissues\Domain\Tracker\Metadata;

interface Storage
{
    function exists($key);
    function get($key);
    function set($key, $value);
}
