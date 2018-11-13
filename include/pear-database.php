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
  +----------------------------------------------------------------------+
*/

require_once 'DB/storage.php';
require_once 'PEAR/Common.php';
require_once __DIR__.'/../src/Category.php';
require_once __DIR__.'/../src/Maintainer.php';
require_once __DIR__.'/../src/Note.php';
require_once __DIR__.'/../src/Package.php';
require_once __DIR__.'/../src/Release.php';
require_once __DIR__.'/../src/User.php';
require_once __DIR__.'/../src/Entity/User.php';

/**
 * Some useful "visitation model" tricks:
 *
 * To find the number of child elements:
 *  (right - left - 1) / 2
 *
 * To find the number of child elements (including self):
 *  (right - left + 1) / 2
 *
 * To get all child nodes:
 *
 *  SELECT * FROM table WHERE left > <self.left> AND left < <self.right>
 *
 * To get all child nodes, including self:
 *
 *  SELECT * FROM table WHERE left BETWEEN <self.left> AND <self.right>
 *  "ORDER BY left" gives tree view
 *
 * To get all leaf nodes:
 *
 *  SELECT * FROM table WHERE right-1 = left;
 */
function renumber_visitations($id, $parent = null)
{
    global $dbh;
    if ($parent === null) {
        $left = $dbh->getOne("select max(cat_right) + 1 from categories
                              where parent is null");
        $left = ($left !== null) ? $left : 1; // first node
    } else {
        $left = $dbh->getOne("select cat_right from categories where id = $parent");
    }
    $right = $left + 1;
    // update my self
    $err = $dbh->query("update categories
                        set cat_left = $left, cat_right = $right
                        where id = $id");
    if (PEAR::isError($err)) {
        return $err;
    }
    if ($parent === null) {
        return true;
    }
    $err = $dbh->query("update categories set cat_left = cat_left+2
                        where cat_left > $left");
    if (PEAR::isError($err)) {
        return $err;
    }
    // (cat_right >= $left) == update the parent but not the node itself
    $err = $dbh->query("update categories set cat_right = cat_right+2
                        where cat_right >= $left and id <> $id");
    if (PEAR::isError($err)) {
        return $err;
    }
    return true;
}
