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
   | Authors: Alan Knowles <alan@akbkhome.com>                                                            |
   +----------------------------------------------------------------------+
   $Id$
*/




/*
*
Notes about this script:
- it is really just includes the body from the /weeklynews/{lang} folder, and adds the list of releases.
- (Very file based)


TODO:
- Change to using the notes methods in pear-deatabase? - if you have the time have a look at this.
however bear in mind that to be comparible to the current method it should attempt to address issues like:
User authentication, revision control, wysiwyg editing. - which currently this (mozilla editor + cvs) offers
- Caching - This does about 5 file requests on an index page, and a medium sized regex + database calls on a news page
- put the articles into year directories
- archived page - look at old stufff.
- Check why gettext functions do not work
*/
@dl('getText.so');

if (!function_exists("getText")) {
    function getText($string) { return $string; }
}


function show_languages($visitlang = "") {
    $available_langs = array(
        'en'    => 'English',
        'fr'    => 'French',
        'de'    => 'German',
        'pt_BR' => 'Brazilian Portuguese',
	'pl'	=> 'Polish'
    );
    echo getText("View In"). ' :: ';
    $page = "";
    if (!$visitlang) { // then you are looking at the archive page
        $page = "archives.html";
    }
    foreach ($available_langs  as $lang => $string) {
        echo "<a href=\"/weeklynews.php/{$lang}/{$page}\">{$string}</a> :: ";
    }
    if ($visitlang) {
        echo "&nbsp;&nbsp;&nbsp;<a href=\"/weeklynews.php/{$visitlang}/archives.html\">". getText("Archives") . "</a>";
    }
}


function show_news_menu() {
    response_header("PEAR Weekly News");
    $lang = "en";
    if (preg_match("/^\/weeklynews.php\/([a-z_]{2,5})\/.*$/i", $_SERVER['REQUEST_URI'],$args)) {
        $lang = $args[1];
    }
    show_languages();
    $dow =  date ("w");
    $start =  mktime (0,0,0,date("m")  ,date("d")-$dow,date("Y"));
    $uweeks = array($start);
    for ($i=1;$i <8;$i++ ) {
        $uweeks[] = mktime (0,0,0,date("m",$start)  ,date("d",$start)-($i*7),date("Y",$start));
    }
    foreach($uweeks as $utime) {
        if (!@file_exists(dirname(__FILE__) . "/../weeklynews/".date("Ymd",$utime). ".{$lang}.html")) {
            continue;
        }
        menu_link(getText("Weekly Summary for") . " " . date("d M Y",$utime), "/weeklynews.php/$lang/".date("Ymd",$utime). ".html");
    }
}


function show_latest($lang) {
    $dow =  date ("w");
    $start =  mktime (0,0,0,date("m")  ,date("d")-$dow,date("Y"));

    for ($i=0;$i <8;$i++ ) {
        $week = mktime (0,0,0,date("m",$start)  ,date("d",$start)-($i*7),date("Y",$start));
        //echo "LOOL FOR $lang" . date("Ymd",$week);
        if (@file_exists(dirname(__FILE__) . "/../weeklynews/".date("Ymd",$week). ".{$lang}.html")) {
            show_news($lang,date("Ymd",$week));
            return TRUE;
        }
    }
}


function get_default_news() {
    response_header(getText("PEAR Weekly News"));
    $lang = "en";
    if (preg_match("/^\/weeklynews.php\/([a-z_]{2,5})\/.*$/i", $_SERVER['REQUEST_URI'],$args)) {
        $lang = $args[1];
    }
    show_languages();
    $dow =  date ("w");
    $start =  mktime (0,0,0,date("m")  ,date("d")-$dow,date("Y"));
    $uweeks = array($start);
    for ($i=1;$i <8;$i++ ) {
        $uweeks[] = mktime (0,0,0,date("m",$start)  ,date("d",$start)-($i*7),date("Y",$start));
    }
    foreach($uweeks as $utime) {
        if (!@file_exists(dirname(__FILE__) . "/../weeklynews/".date("Ymd",$utime). ".{$lang}.html")) {
            continue;
        }
        menu_link(getText("Weekly Summary for ") . date("d M Y",$utime), "/weeklynews.php/$lang/".date("Ymd",$utime). ".html");
    }
}


function show_news($lang,$date) {
    $end =  mktime (0,0,0,substr($date,4,2)  ,substr($date,6,2),substr($date,0,4));
    $start =  mktime(0,0,0,date("m",$end)  ,date("d",$end)-7,date("Y",$end));

    response_header(getText("PEAR Weekly News") . " - ". date("d M Y", $end) );
    show_languages($lang);
    echo "<H1>". getText("PEAR Weekly News for week ending"). " " . date("d M Y", $end) . "</H1>";


    $summary = implode('',file(dirname(__FILE__) . "/../weeklynews/$date.{$lang}.html"));
    // get the body!
    $summary = preg_replace("/^(.*)<body/si", "",$summary);
    $summary = preg_replace("/^([^>]*)>/si", "",$summary);
    $summary = preg_replace("/<\/body>.*/si", "",$summary);
    echo $summary;
    echo "<HR>";
    echo "<H1>". getText("Packages Released This Week"). "</H1>";

    $recent = release::getDateRange($start,$end);
    $out = "";
    if (@sizeof($recent) > 0) {
        $out = "";
        $out .= "<table>";
        foreach ($recent as $release) {
            extract($release);
            $releasedate = substr($releasedate, 0, 10);
            $desc = substr($releasenotes, 0, 40);
            if (strlen($releasenotes) > 40) {
                $desc .= '...';
            }
            $out .= "<tr><td valign='top' class='compact'>";
            $out .= "<h3><a href=\"/package-info.php?pacid=$id&release=$version\">";
            $out .= "$name</a></H3> ". nl2br($description);
            $out .= "<P><B>".getText("Release")." $version - $state</B> <i>$releasedate:</i><BR>". nl2br($releasenotes);
            $out .= "</td></tr>";
        }
        $out .= "</table>\n";
    }
    echo $out;
}

/**
* Main part of script -- this really needs tidying up..
*/
$args = array();
$show_archives = FALSE;
$show_latest = TRUE;
$show_lang = "en";
if (preg_match("/^\/weeklynews.php\/([a-z_]{2,5})/i",$_SERVER['REQUEST_URI'],$args)) {
    // #TODO - can somebody fix this it just doesnt work on my test machine!
    $lang_maps = array(
        "en"    => "en_US",
        "de"    => "de",
        "fr"    => "fr",
        "pt_BR" => "pt_BR",
        "pl"    => "pl"
    );

    $iso_maps = array(
        "en"        => "iso-8859-1",
        "de"        => "iso-8859-1",
        "fr"        => "iso-8859-1",
        "pt_BR"     => "iso-8859-1",
        "pl"        => "iso-8859-2"
    );    

    $show_lang = $args[1];
    $locale = $lang_maps[$args[1]];
    setlocale(LC_ALL, $locale);
    header("Content-Type: text/html; charset=".$iso_maps[$show_lang]);

    if (function_exists("bindtextdomain")) {
        bindtextdomain("weeklynews", "../weeklynews/locale");
        textdomain("weeklynews");
    }
}

if (preg_match("/^\/weeklynews.php\/([a-z_]{2,5})\/([0-9]+)\.html$/i", $_SERVER['REQUEST_URI'],$args)) {
    if (@file_exists(dirname(__FILE__) . "/../weeklynews/".$args[2] . "." .$args[1] .".html")) {
        $show_latest = FALSE;
    }
}
if ($show_latest && preg_match("/^\/weeklynews.php\/([a-z_]{2,5})\/archives\.html$/i", $_SERVER['REQUEST_URI'],$args)) {
    $show_archives = TRUE;

}

if ($show_archives) {
    show_news_menu();
} elseif ($show_latest) {
    if (!show_latest($show_lang)) {
        show_news_menu();
    }
} else {
    show_news($args[1],$args[2]);
}
// has the url got some requiest?

response_footer();
?>
