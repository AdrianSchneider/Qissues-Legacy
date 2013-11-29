<?php

namespace Qissues\Trackers\Shared\Support;

class SupportLevel
{
    const NONE     = 'none';
    const SINGLE   = 'single';
    const MULTIPLE = 'multiple';
    const DYNAMIC  = 'dynamic';

    protected $level;
    protected $levels = array(
        self::NONE     => 1,
        self::SINGLE   => 2,
        self::MULTIPLE => 4,
        self::DYNAMIC  => 8
    );

    public function __construct()
    {
        $this->level = self::NONE;
    }

    /**
     * Sets support for a given level
     *
     * @param string level
     * @return SupportLevel $this
     * @throws InvalidArgumentException with invalid level
     * @throws DomainException when rules conflict
     */
    public function set($level)
    {
        if (!isset($this->levels[$level])) {
            throw new \InvalidArgumentException('Invalid level');
        }
        if ($level == self::SINGLE and $this->supports(self::MULTIPLE)) {
            throw new \DomainException('Cannot remark a multiple-value feature as single');
        }
        if ($level == self::MULTIPLE and $this->supports(self::SINGLE)) {
            throw new \DomainException('Cannot remark a single-value feature as multiple');
        }

        $this->level ^= $this->levels[self::NONE];
        $this->level |= $this->levels[$level];

        return $this;
    }

    /**
     * Check support for type
     * @param integer $level
     * @return boolean true if supported
     */
    public function supports($type)
    {
        return !!($this->level & $this->levels[$type]);
    }

    /**
     * Check if there is any support at all
     * @return boolean true if not none
     */
    public function isSupported()
    {
        return !($this->level & self::NONE);
    }
}
