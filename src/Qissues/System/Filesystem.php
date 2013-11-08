<?php

namespace Qissues\System;

use Symfony\Component\Filesystem\Filesystem as BaseFilesystem;

class Filesystem extends BaseFilesystem
{
    public function read($filename)
    {
        return file_get_contents($filename);
    }
}
