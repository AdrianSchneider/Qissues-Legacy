<?php

namespace Qissues\Console\Input;

class ExternalFileEditor
{
    protected $editor;
    protected $prefix;

    public function __construct($editor = null, $prefix = 'qissues')
    {
        $this->prefix = $prefix;
        if (!$this->editor = $editor ?: getenv('VISUAL') ?: getenv('EDITOR')) {
            throw new \Exception('No editor specified');
        }
    }

    /**
     * Loads $template into file, and returns the contents
     * once the editor has closed
     *
     * @param string $template
     * @return string edited file contents
     */
    public function getEdited($template)
    {
        $filename = $this->createTempFile($template);

        exec(sprintf(
            '%s %s > `tty`',
            $this->editor,
            escapeshellarg($filename)
        ));

        return $this->flushTempFile($filename);
    }

    /**
     * Creates a temp file and returns the name
     * @param string $content file contents
     * @return string filename
     */
    protected function createTempFile($content)
    {
        $filename = tempnam('.', $this->prefix);
        file_put_contents($filename, $content);

        return $filename;
    }

    /**
     * Deletes the temp file and returns its contents
     * @param string $filename
     * @return string content
     */
    protected function flushTempFile($filename)
    {
        $content = file_get_contents($filename);
        unlink($filename);
        return $content;
    }
}
