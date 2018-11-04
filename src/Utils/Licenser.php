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
  | Authors:                                                             |
  +----------------------------------------------------------------------+
*/

namespace App\Utils;

/**
 * Helper utility class to get URL by given license name.
 *
 * @param string Name of the license
 */
class Licenser
{
    /**
     * Returns a link to license by its name.
     */
    public function getUrl($license)
    {
        switch ($license) {
            case 'PHP License':
            case 'PHP 3.01':
            case 'PHP License 3.01':
            case 'PHP':
                return 'https://php.net/license/3_01.txt';
            break;
            case 'PHP 3.0':
            case 'PHP License 3.0':
                return 'https://php.net/license/3_0.txt';
            break;
            case 'PHP 2.02':
            case 'PHP License 2.02':
                return 'https://php.net/license/2_02.txt';
            break;
            case 'LGPL':
            case 'GNU Lesser General Public License':
                return 'https://www.gnu.org/licenses/lgpl.html';
            break;
        }

        return '';
    }

    /**
     * Get possible HTML anchor element from the given license name.
     */
    public function getHtml($license = '')
    {
        $url = $this->getUrl($license);

        return ($url != '' ? '<a href="'.$url.'">'.$license.'</a>' : $license);
    }
}
