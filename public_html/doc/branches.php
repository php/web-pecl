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

use App\Template\Engine;

require_once __DIR__.'/../../include/pear-prepend.php';

$template = new Engine(__DIR__.'/../../templates');

$template->register('getImageSize', [$imageSize, 'getSize']);

echo $template->render('pages/doc/branches.php', [
    'scheme' => $config->get('scheme'),
    'host' => $config->get('host'),
    'auth' => $auth,
    'authUser' => $auth_user,
    'lastUpdated' => $LAST_UPDATED,
    'onloadInlineJavaScript' => isset($GLOBALS['ONLOAD']) ? $GLOBALS['ONLOAD'] : '',
]);
