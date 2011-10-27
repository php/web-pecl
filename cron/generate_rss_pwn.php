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
// |  Authors:  Pierre-Alain Joye <paj@pearfr.org>                       |
// +---------------------------------------------------------------------+
//

require_once dirname(__FILE__) . '/../include/rss.php';

foreach($lang_maps as $id_lang=>$lang){
    list($date,$pwn_html_path) = show_latest($id_lang);
    $fp = @fopen($pwn_html_path,'r');
    if($fp){
        $content = "";
        while(!feof($fp)){
            $content .= fgets($fp, 1024);
        }
        fclose($fp);

        $sections       = explode("<!-- SECTION -->",$content);
        $cnt_sections   = sizeof($sections);
        $rdfsequences   = "";
        $items          = "";
        for($i=1;$i<$cnt_sections;$i++){
            preg_match("/<h3>(.*)<\/h3>/",$sections[$i],$titles);
            $section = str_replace(	array($titles[0],"\n"),
                                    array('',''),
                                    $sections[$i]
                        );

            $section = strip_tags(preg_replace('/<p>/si',"\n",$section));
            $rdfsequences .= str_replace('{link}',  $titles[1], $rdfseq);
            $cur_item = str_replace('{link_about}', PEAR_SITE_PWN.$i, $item);
            $cur_item = str_replace('{title}',      $titles[1], $cur_item);
            $cur_item = str_replace('{link}',       $titles[1], $cur_item);
            $cur_item = str_replace('{description}',$section, $cur_item);
            $items .= str_replace('{date}',         '$date', $cur_item);
        }
        $pub_date = date("Y-m-d, H:i");
        $rss	= str_replace('{encoding}',$iso_maps[$id_lang],$head);
        $rss	= str_replace('{pub_date}',$pub_date,$rss);
        $rss 	= str_replace('{rdfsequences}',$rdfsequences,$rss).$items.$footer;
        $rss_file = PEAR_RSS_PATH.'/'.'pwn_'.$id_lang.'.rss';
        $fp = fopen($rss_file,'w');
        if($fp){
            fputs($fp,$rss);
            fclose($fp);
        }
    }
}

?>