<?php

/*
  +----------------------------------------------------------------------+
  | The PECL website                                                     |
  +----------------------------------------------------------------------+
  | Copyright (c) 1999-2018 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | https://php.net/license/3_01.txt                                     |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors: Stig S. Bakken <ssb@fast.no>                                |
  |          Tomas V.V.Cox <cox@php.net>                                 |
  |          Martin Jansen <mj@php.net>                                  |
  |          Gregory Beaver <cellog@php.net>                             |
  |          Richard Heyes <richard@php.net>                             |
  |          Peter Kokot <petk@php.net>                                  |
  +----------------------------------------------------------------------+
*/

namespace App\Utils;

/**
 * Helper for formatting given date and changing it to UTC time zone. Production
 * server operates with UTC time zone.
 */
class FormatDate
{
    /**
     * Convert given datetime string into a formatted string in the UTC time zone.
     *
     * @param string $string Time given in format YYYY-MM-DD HH:II:SS from the
     *                       local machine. If none is provided the current
     *                       time is used.
     * @param string $format a format string, as per https://php.net/date
     *
     * @return string Formatted time in UTC time zone
     */
    public function utc($string = null, $format = null)
    {
        if (empty($string) || '0000-00-00 00:00:00' === $string) {
            $string = date('Y-m-d H:i:s');
        }

        $date = \DateTime::createFromFormat('Y-m-d H:i:s', $string);
        $date->setTimezone(new \DateTimeZone('UTC'));

        return $date->format(($format) ? $format : 'Y-m-d H:i \U\T\C');
    }
}
