<?php

namespace Qissues\Model\Tracker\Support;

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
     * Adds single item support
     */
    public function setSingle()
    {
        if ($this->supports(self::MULTIPLE)) {
            throw new \DomainException('Cannot remark a multiple-value feature as single');
        }
        
        $this->level ^= $this->levels[self::NONE];
        $this->level |= $this->levels[self::SINGLE];

        return $this;
    }

    /**
     * Adds multiple item support
     */
    public function setMultiple()
    {
        if ($this->supports(self::SINGLE)) {
            throw new \DomainException('Cannot remark a single-value feature as multiple');
        }

        $this->level ^= $this->levels[self::NONE];
        $this->level |= $this->levels[self::MULTIPLE];

        return $this;
    }

    /**
     * Adds dynamic item support
     */
    public function setDynamic()
    {
        $this->level ^= $this->levels[self::NONE];
        $this->level |= $this->levels[self::DYNAMIC];

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
