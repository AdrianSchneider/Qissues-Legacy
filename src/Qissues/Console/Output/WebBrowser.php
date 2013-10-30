<?php

namespace Qissues\Console\Output;

class WebBrowser
{
    public function __construct($browser)
    {
        $this->browser = $browser;
    }

    protected function prepareBrowser()
    {
        if ($this->browser) {
            return $this->browser;
        }

        $uname = strtolower(php_uname());
        if (strpos($uname, "linux") !== false) {
            return $this->browser = 'xdg-open';
        }
        if (strpos($uname, "darwin") !== false) {
            return $this->browser = 'open';
        }

        throw new \RunTimeException('Your operating system is not supported; set console.browser.command in ~/.qissues');
    }

    public function open($url)
    {
        exec(sprintf('%s %s', $this->prepareBrowser(), escapeshellarg($url)));
    }
}
