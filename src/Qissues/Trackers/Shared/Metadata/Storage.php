<?php

namespace Qissues\Trackers\Shared\Metadata;

interface Storage
{
    function exists($key);
    function get($key);
    function set($key, $value);
}
