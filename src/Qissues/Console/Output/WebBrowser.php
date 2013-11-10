<?php

namespace Qissues\Console\Output;

use Qissues\Console\Shell\Shell;

class WebBrowser
{
    protected $shell;
    protected $browser;

    /**
     * The default browser to use
     * @param string|null $browser
     */
    public function __construct(Shell $shell, $browser = null)
    {
        $this->shell = $shell;
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

        $environment = strtolower($this->shell->run('uname'));

        if (strpos($environment, "linux") !== false) {
            return $this->browser = 'xdg-open';
        }
        if (strpos($environment, "darwin") !== false) {
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
        $this->shell->run(sprintf('%s %s', $this->prepareBrowser(), $this->shell->escape($url)));
    }
}
