<?php

namespace Qissues\Console\Output;

class WebBrowser
{
    public function __construct($browser)
    {
        $this->browser = $browser;
    }

    public function open($url)
    {
        exec(sprintf('%s %s', $this->browser, escapeshellarg($url)));
    }
}
