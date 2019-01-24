<?php

/*
  +----------------------------------------------------------------------+
  | The PECL website                                                     |
  +----------------------------------------------------------------------+
  | Copyright (c) 1999-2019 The PHP Group                                |
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

require_once __DIR__.'/../include/pear-prepend.php';

// Array of lists (list, name, short desc., moderated, archive, digest, newsgroup)
$mailingLists = [
    [
        'handle' => 'pecl-dev',
        'title' => 'PECL developers list',
        'description' => 'A list for developers of PECL',
        'moderated' => 'no',
        'archive' => true,
        'digest' => 'available',
        'newsgroup' => 'php.pecl.dev',
    ],
    [
        'handle' => 'pecl-cvs',
        'title' => 'PECL SVN list',
        'description' => 'All the commits of the svn PECL code repository are posted to this list automatically',
        'moderated' => 'no',
        'archive' => true,
        'digest' => 'n/a',
        'newsgroup' => 'php.pecl.cvs'
    ],
];

$icons = [
    [
        'file' => 'pecl-power.gif',
        'description' => 'Powered by PECL, GIF format',
        'dimensions' => '',
        'size' => '',
    ],
    [
        'file' => 'pecl-power.png',
        'description' => 'Powered by PECL, PNG format',
        'dimensions' => '',
        'size' => '',
    ],
    [
        'file' => 'pecl-icon.gif',
        'description' => 'PECL icon, GIF format',
        'dimensions' => '',
        'size' => '',
    ],
    [
        'file' => 'pecl-icon.png',
        'description' => 'PECL icon, PNG format',
        'dimensions' => '',
        'size' => '',
    ]
];

foreach ($icons as $key => $icon) {
    if ($size = @getimagesize(__DIR__.'/img/'.$icon['file'])) {
        $icons[$key]['dimensions'] = $size[0].' x '.$size[1].' pixels';
    }

    if ($size = @filesize(__DIR__.'/img/'.$icon['file'])) {
        $icons[$key]['size'] = $size.' bytes';
    }
}

echo $template->render('pages/support.php', [
    'lists' => $mailingLists,
    'icons' => $icons,
]);
