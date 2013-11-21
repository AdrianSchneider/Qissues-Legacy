<?php

namespace Qissues\Model\Tracker\Metadata;

interface Storage
{
    function exists($key);
    function get($key);
    function set($key, $value);
}
