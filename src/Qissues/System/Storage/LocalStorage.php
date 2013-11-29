<?php

namespace Qissues\System\Storage;

use Qissues\Application\Tracker\Metadata\Storage;
use Qissues\System\Filesystem;

class LocalStorage implements Storage
{
    protected $filesystem;
    protected $filename;
    protected $data;

    /**
     * Prepares local storage for use, creating file if it does not exist
     *
     * @param Filesystem $filesystem
     * @param string $filename
     */
    public function __construct(Filesystem $filesystem, $filename)
    {
        if (function_exists('posix_getuid') && strpos($filename, '~') !== false) {
            $info = posix_getpwuid(posix_getuid());
            $filename = str_replace('~', $info['dir'], $filename);
        }

        $this->filesystem = $filesystem;
        $this->filename = $filename;

        if ($this->filesystem->exists($filename)) {
            $this->data = json_decode($this->filesystem->read($filename), true);
        } else {
            $this->filesystem->dumpFile($filename, '{}');
            $this->data = array();
        }
    }

    /**
     * Checks to see if a key exists
     * @param string $key
     * @return boolean true if exists
     */
    public function exists($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Retrieves a key from storage
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        if (!isset($this->data[$key])) {
            throw new \InvalidArgumentException("$key does not exist in storage");
        }

        return $this->data[$key];
    }

    /**
     * Updates a key in storage
     *
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
        $this->filesystem->dumpFile($this->filename, json_encode($this->data));
    }
}
