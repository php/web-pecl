#!/usr/local/bin/php -Cq
<?php
/* vim: set expandtab tabstop=4 shiftwidth=4; */
// +---------------------------------------------------------------------+
// |  PHP version 4.0                                                    |
// +---------------------------------------------------------------------+
// |  Copyright (c) 1997-2003 The PHP Group                              |
// +---------------------------------------------------------------------+
// |  This source file is subject to version 2.0 of the PHP license,     |
// |  that is bundled with this package in the file LICENSE, and is      |
// |  available through the world-wide-web at                            |
// |  http://www.php.net/license/2_02.txt.                               |
// |  If you did not receive a copy of the PHP license and are unable to |
// |  obtain it through the world-wide-web, please send a note to        |
// |  license@php.net so we can mail you a copy immediately.             |
// +---------------------------------------------------------------------+
// |  Authors:  Pierre-Alain Joye <paj@php.net>                          |
// +---------------------------------------------------------------------+
//


require_once 'DB.php';
require_once dirname(__FILE__) . '/../include/pear-database.php';
require_once dirname(__FILE__) . '/../include/rss.php';

if (DB::isError($dbh = DB::connect(DSN))) {
    die ("Couldn't open database -> ".DSN."\n");
}

$dow =  date ("w");
$end	=  mktime (0,0,0,date("m")  ,date("d")-$dow,date("Y"));
$start	=  mktime(0,0,0,date("m",$end)  ,date("d",$end)-7,date("Y",$end));

$recent = release::getDateRange($start,$end);
$out = "";
if (@sizeof($recent) > 0) {
    $items='';
    $rdfsequences='';
    foreach ($recent as $release) {
        extract($release);

        $releasedate = substr($releasedate, 0, 10);
        $desc = substr($releasenotes, 0, 40);
        if (strlen($releasenotes) > 40) {
            $desc .= '...';
        }

        //$description = str_replace("\n",'',$description);
        $description = strip_tags($description);
        $link_about	 = htmlentities(PEAR_SITE."/package-info.php?pacid=$id");
        $link 		 = $link_about.htmlentities("&release=$version");
        $title	= "$name $version ($state)";


        $rdfsequences .= str_replace('{link}',  $name, $rdfseq);
        $cur_item = str_replace('{link_about}', $link_about, $item);
        $cur_item = str_replace('{title}',      $title, $cur_item);
        $cur_item = str_replace('{link}',       $link, $cur_item);
        $cur_item = str_replace('{description}',$description, $cur_item);
        $items .= str_replace('{date}',         $releasedate, $cur_item);
	}
    $pub_date = date("Y-m-d, H:i");

    $rss    = str_replace('{encoding}',$iso_maps['en'],$head);
    $rss    = str_replace('{pub_date}',$pub_date,$rss);
    $rss    = str_replace('{rdfsequences}',$rdfsequences,$rss).$items.$footer;
    $rss_file = PEAR_RSS_PATH.'/'.'releases.rss';
    $fp = fopen($rss_file,'w');
    if($fp){
        fputs($fp,$rss);
        fclose($fp);
    }
}
?>