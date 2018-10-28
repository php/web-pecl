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

/**
 * Returns an IMG tag for a given file (relative to the images dir)
 */
function make_image($file, $alt = '', $align = '', $extras = '', $dir = '',
                    $border = 0, $styles = '')
{
    if (!$dir) {
        $dir = '/gifs';
    }
    if ($size = @getimagesize($_SERVER['DOCUMENT_ROOT'].$dir.'/'.$file)) {
        $image = sprintf('<img src="%s/%s" style="border: %d;%s%s" %s alt="%s" %s />',
            $dir,
            $file,
            $border,
            ($styles ? ' '.$styles            : ''),
            ($align  ? ' float: '.$align.';'  : ''),
            $size[3],
            ($alt    ? $alt : ''),
            ($extras ? ' '.$extras            : '')
        );
    } else {
        $image = sprintf('<img src="%s/%s" style="border: %d;%s%s" alt="%s" %s />',
            $dir,
            $file,
            $border,
            ($styles ? ' '.$styles            : ''),
            ($align  ? ' float: '.$align.';'  : ''),
            ($alt    ? $alt : ''),
            ($extras ? ' '.$extras            : '')
        );
    }
    return $image;
}

// make_link()
// return a hyperlink to something, within the site
//

function make_link ($url, $linktext=false, $target=false, $extras=false) {
    return sprintf("<a href=\"%s\"%s%s>%s</a>",
        $url,
        ($target ? ' target="'.$target.'"' : ''),
        ($extras ? ' '.$extras : ''),
        ($linktext ? $linktext : $url)
    );
}

// make_mailto_link()
// return a mailto-hyperlink
//

function make_mailto_link ($url, $linktext=false, $extras=false) {
    return make_link("mailto:" . $url, ($linktext ? $linktext : $url), false, $extras);
}

// print_link()
// echo a hyperlink to something, within the site
//

function print_link($url, $linktext=false, $target=false, $extras=false) {
    echo make_link($url, $linktext, $target, $extras);
}

// make_bug_link()
// creates a link for the bug system

function make_bug_link($package, $type = 'list', $linktext = false) {
    switch ($type) {
        case 'list':
            if (!$linktext) {
                $linktext = 'Package Bugs';
            }
            return make_link('https://bugs.php.net/search.php?cmd=display&status=Open&package_name[]='.$package, $linktext);
        case 'report':
            if (!$linktext) {
                $linktext = 'Report a new bug';
            }
            return make_link("https://bugs.php.net/report.php?package=$package", $linktext);
    }

}
