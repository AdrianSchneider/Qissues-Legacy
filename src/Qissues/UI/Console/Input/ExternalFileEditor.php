<?php

namespace Qissues\Console\Input;

use Qissues\Console\Shell\Shell;
use Qissues\System\Filesystem;

class ExternalFileEditor
{
    protected $shell;
    protected $filesystem;
    protected $editor;
    protected $filename;

    public function __construct(Shell $shell, Filesystem $filesystem, $editor = null, $filename = '.qissues.tmp')
    {
        $this->shell = $shell;
        $this->filesystem = $filesystem;
        $this->filename = $filename;

        if (!$this->editor = $editor ?: getenv('VISUAL') ?: getenv('EDITOR')) {
            throw new \BadMethodCallException('No editor specified');
        }
    }

    /**
     * Gets the selected editor name
     * @return string
     */
    public function getEditor()
    {
        return $this->editor;
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
        $this->filesystem->dumpFile($this->filename, $template);

        $this->shell->run(sprintf(
            '%s %s > `tty`',
            $this->editor,
            escapeshellarg($this->filename)
        ));

        return $this->flushTempFile($this->filename);
    }

    /**
     * Deletes the temp file and returns its contents
     * @return string content
     */
    protected function flushTempFile()
    {
        $content = $this->filesystem->read($this->filename);
        $this->filesystem->remove($this->filename);
        return $content;
    }
}
