<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001 The PHP Group                                     |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors:                                                             |
   +----------------------------------------------------------------------+
   $Id$
*/

if (empty($prevsearch)) $prevsearch = '';

// spacer()
// print a IMG tag for a sized spacer GIF
//

function spacer($width=1, $height=1, $align=false, $extras=false) {
    printf('<img src="/gifs/spacer.gif" width="%d" height="%d" border="0" alt="" %s%s />',
        $width,
        $height,
        ($align ? 'align="'.$align.'" ' : ''),
        ($extras ? $extras : '')
    );
}



// resize_image()
// tag the output of make_image() and resize it manually
//

function resize_image($img, $width=1, $height=1) {
    $str = preg_replace('/width=\"([0-9]+?)\"/i', '', $img );
    $str = preg_replace('/height=\"([0-9]+?)\"/i', '', $str );
    $str = substr($str,0,-1) . sprintf(' height="%s" width="%s" />', $height, $width );
    return $str;
}



// make_image()
// return an IMG tag for a given file (relative to the images dir)
//

function make_image($file, $alt=false, $align=false, $extras=false, $dir=false, $border=0) {
    if (!$dir) {
        $dir = '/gifs';
    }
    if ($size = @getimagesize($_SERVER['DOCUMENT_ROOT'].$dir.'/'.$file)) {
        $image = sprintf('<img src="%s/%s" border="%d" %s alt="%s" %s%s />',
            $dir,
            $file,
            $border,
            $size[3],
            ($alt    ? $alt : ''),
            ($align  ? ' align="'.$align.'"'  : ''),
            ($extras ? ' '.$extras            : '')
        );
    } else {
        $image = sprintf('<img src="%s/%s" border="%d" alt="%s" %s%s />',
            $dir,
            $file,
            $border,
            ($alt    ? $alt : ''),
            ($align  ? ' ALIGN="'.$align.'"'  : ''),
            ($extras ? ' '.$extras            : '')
        );
    }
    return $image;
}



// print_image()
// print an IMG tag for a given file
//

function print_image($file, $alt=false, $align=false, $extras=false, $dir=false, $border=0) {
    print make_image($file, $alt, $align, $extras, $dir);
}

// delim()
// print a pipe delimiter
//

function delim($color=false) {
    if (!$color) {
        return '&nbsp;|&nbsp;';
    }
    return sprintf('<font color="%s">&nbsp;|&nbsp;</font>', $color );
}



// hdelim()
// print a horizontal delimiter (just a wide line);
//

function hdelim() {
    return '<hr />';
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
            return make_link('/bugs/search.php?cmd=display&status=Open&bug_type[]='.$package, $linktext);
        case 'report':
            if (!$linktext) {
                $linktext = 'Report a new bug';
            }
            return make_link("/bugs/report.php?package=$package", $linktext);
    }

}
?>
