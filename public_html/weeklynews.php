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
 

require_once 'Date.php' ;



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

*/
 
function show_languages() {
    $available_langs = array(
        'en' => 'English',
        'fr' => 'French'
    );
    echo 'View In :: ';
    foreach ($available_langs  as $lang => $string) {
        echo "<a href=\"/weeklynews.php/{$lang}/\">{$string}</a> :: ";
    }
}


function show_news_menu() {
    response_header("PEAR Weekly News");
    $lang = "en";
    if (preg_match("/^\/weeklynews.php\/([a-z]{2})\/.*$/", $_SERVER['REQUEST_URI'],$args)) {
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
        menu_link("Weekly Summary for " . date("d M Y",$utime), "/weeklynews.php/$lang/".date("Ymd",$utime). ".html");
    }
}

function show_news($lang,$date) {
    $week = new Date( $date . "000000");
    $end= $week->getTime();
    $start =  mktime(0,0,0,date("m",$end)  ,date("d",$end)-7,date("Y",$end));
    
    response_header("PEAR Weekly News - ". $week->format("%d %B %Y") );
    echo "<H1>PEAR Weekly News for week ending " . $week->format("%d %B %Y") . "</H1>";
    
    
    $summary = implode('',file(dirname(__FILE__) . "/../weeklynews/$date.{$lang}.html"));
    // get the body!
    $summary = preg_replace("/^(.*)<body/si", "",$summary);
    $summary = preg_replace("/^([^>]*)>/si", "",$summary);
    $summary = preg_replace("/<\/body>.*/si", "",$summary);
    echo $summary;
    echo "<HR>";
    echo "<H1>Packages Released This Week</H1>";
    
      
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
            $out .= "<a href=\"/package-info.php?pacid=$id&release=$version\">";
//            $RSIDEBAR_DATA .= "$name $version</a><br /><font size=\"-1\" face=\"arial narrow,arial,helvetica,sans-serif\"><i>$releasedate:</i>$desc</font></td></tr>";
            $out .= "$name</a><br /> ". nl2br($description);
            $out .= "<P><B>Release $version - $state</B> <i>$releasedate:</i><BR>". nl2br($releasenotes);
            $out .= "</td></tr>";
        }
        $out .= "</table>\n";
    }
    echo $out;
    
    
    
      
}                  
  
/**
* Main part of script
*/ 
$args = array();
$show_menu = TRUE;
if (preg_match("/^\/weeklynews.php\/([a-z]{2})\/([0-9]+)\.html$/", $_SERVER['REQUEST_URI'],$args)) {
    if (@file_exists(dirname(__FILE__) . "/../weeklynews/".$args[2] . "." .$args[1] .".html")) { 
        $show_menu = FALSE;
    }
}
if ($show_menu) {
    show_news_menu();
} else {
    show_news($args[1],$args[2]);
}
// has the url got some requiest?





 
 
response_footer();
?>
