<?php

namespace Qissues\Domain\Tracker\Metadata;

class Builder
{
    protected $storage;
    protected $tracker;
    protected $type;

    /**
     * @param Storage $storage
     * @param string $tracker name
     * @param string $type class name
     */
    public function __construct(Storage $storage, $tracker, $type)
    {
        $this->storage = $storage;
        $this->tracker = $tracker;
        $this->type = $type;
    }

    /**
     * Builds metadata of type $type from storage
     * @return $type on success, NullMetadata on failure
     */
    public function build()
    {
        if ($this->storage->exists($id = $this->getId())) {
            return new $this->type($this->storage->get($id));
        }

        return new NullMetadata();
    }

    /**
     * Generates the storage id
     * @return string tracker-cwd
     */
    protected function getId()
    {
        return sprintf('%s-%s', $this->tracker, getcwd());
    }
}
