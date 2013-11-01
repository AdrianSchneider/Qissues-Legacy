<?php

namespace Qissues\Console\Output;

class WebBrowser
{
    protected $browser;

    /**
     * The default browser to use
     * @param string|null $browser
     */
    public function __construct($browser)
    {
        $this->browser = $browser;
    }

    /**
     * Ensure there is a browser we can call
     * @return string browser binary
     * @throws RunTimeException when none could be auto-detected
     */
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

    /**
     * Open a URL with the system browser
     * @param string @url
     */
    public function open($url)
    {
        exec(sprintf('%s %s', $this->prepareBrowser(), escapeshellarg($url)));
    }
}
