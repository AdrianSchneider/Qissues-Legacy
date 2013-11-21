<?php

namespace Qissues\System\Util;

class LocalTime
{
    static protected $offset;

    static protected function getOffset()
    {
        if (!self::$offset) {
            $date = new \DateTime;
            self::$offset = $date->format('Z');
        }
        return self::$offset;
    }

    static public function convert(\DateTime $date)
    {
        $date = clone $date;
        $date->modify(sprintf('+%d seconds', self::getOffset()));
        return $date;
    }
}
