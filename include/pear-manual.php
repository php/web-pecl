<?php // -*- C++ -*-
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

require_once "site.php";

$NEXT = $PREV = $UP = $HOME = array(false, false);
$TOC = array();

$SIDEBAR_DATA = '';

function setupNavigation($data) {
    global $NEXT, $PREV, $UP, $HOME, $TOC, $tstamp;
    $HOME = @$data["home"];
    $HOME[0] = "./";
    $NEXT = @$data["next"];
    $PREV = @$data["prev"];
    $UP   = @$data["up"];
    $TOC =  @$data["toc"];
    $tstamp = gmdate("D, d M Y",getlastmod());
}

function makeBorderTOC($this) {
    global $NEXT, $PREV, $UP, $HOME, $TOC, $DOCUMENT_ROOT;
    global $SIDEBAR_DATA, $LANG,$CHARSET;

    $SIDEBAR_DATA = '<form method="get" action="/manual-lookup.php">' .
    $SIDEBAR_DATA.= '<table border="0" cellpadding="4" cellspacing="0">';

    /** The manual lookup will be implemented at a later point.
    $SIDEBAR_DATA.= '<tr valign="top"><td><small>' .
        '<input type="hidden" name="lang" value="' . $LANG . '">' .
        'lookup: <input type="text" class="small" name="function" size="10"> ' .
        make_submit('small_submit_white.gif', 'lookup', 'bottom') .
        '<br /></small></td></tr>';

    $SIDEBAR_DATA.= '<tr bgcolor="#cccccc"><td></td></tr>';
    */

    $SIDEBAR_DATA.= '<tr valign="top"><td>' . 
        make_link('./', make_image('caret-t.gif', $HOME[1]) . $HOME[1] ) . 
        '<br /></td></tr>';

    $SIDEBAR_DATA.= '<tr bgcolor="#cccccc"><td></td></tr>';

    if (($HOME[1] != $UP[1]) && $UP[1]) {
        $SIDEBAR_DATA.= '<tr valign="top"><td>' . 
            make_link($UP[0], make_image('caret-u.gif', $UP[1]) . $UP[1] ) . 
            '<br /></td></tr>';
    }

    $SIDEBAR_DATA.= '<tr valign="top"><td><small>';

    for ($i = 0; $i < count($TOC); $i++) {
        list($url, $title) = $TOC[$i];
        if (!$url || !$title) {
            continue;
        }
        $img = 'box-0.gif';
        if ($title == $this) {
            $img = 'box-1.gif';
        }

        $SIDEBAR_DATA .= '&nbsp;' . 
            make_link($url, make_image($img, htmlspecialchars($title,ENT_QUOTES,$CHARSET)) . htmlspecialchars($title,ENT_QUOTES,$CHARSET) ) . 
            '<br />';
    }

    $SIDEBAR_DATA.= '</small></td></tr>';

    if (count($TOC) > 1) {
        $SIDEBAR_DATA.= '<tr bgcolor="#cccccc"><td></td></tr>';
    }

    $SIDEBAR_DATA .= '<tr valign="top"><td><small>&nbsp;'
                     . make_image("box-0.gif")
                     . make_link("/download-docs.php", "Download Documentation")
                     . '</small></td></tr>';

    $SIDEBAR_DATA.= '</table></form>';

}

function navigationBar($title,$id,$loc) {
    global $NEXT, $PREV, $tstamp,$CHARSET;

    echo '<table border="0" width="620" bgcolor="#e0e0e0" cellpadding="0" cellspacing="4">';

    echo '<tr><td>';
    if ($PREV[1]) {
        print_link( $PREV[0] , make_image('caret-l.gif', 'previous') .  htmlspecialchars($PREV[1],ENT_QUOTES,$CHARSET) ) ;
    }
    echo '<br /></td>';

    echo '<td align="right">';
    if ($NEXT[1]) {
        print_link( $NEXT[0] , htmlspecialchars($NEXT[1],ENT_QUOTES,$CHARSET)  . make_image('caret-r.gif', 'next') ) ;
    }
    echo '<br /></td>';
    echo '</tr>';

    echo '<tr bgcolor="#cccccc"><td colspan="2">';
    spacer(1,1);
    echo '<br /></td></tr>';

    if ($loc != 'bottom') {
        global $LANGUAGES;
        $links = array();
        foreach($LANGUAGES as $code => $name) {
            if (file_exists("../$code/$id")) {
                $links[] = make_link("../$code/$id", $name);
            }
        }
        $file = substr($id,0,-4);
        if (file_exists("html/$file.html")) {
            $links[] = make_link("html/$file.html", 'Plain HTML');
        }
        echo '<tr>';
        if (count($links)) {
            echo '<td><small>View this page in ' . join (delim(), $links) . '</small></td>';
        }
        echo '<td align="right"><small>Last updated: '.$tstamp.'</small></td></tr>';
    } else {
        echo '<tr>';
        echo '<td valign="top" align="left"><small>'
             . make_link("/download-docs.php", "Download Documentation")
            . '</small</td>';
        echo '<td align="right"><small>Last updated: '.$tstamp.'<br />';
    }

    echo '</small></td></tr>';
    echo "</table>\n";

}

function sendManualHeaders($charset,$lang) {
        global $LANG,$CHARSET;
        $LANG = $lang;
        $CHARSET = $charset;
    Header("Cache-Control: public, max-age=600");
    Header("Vary: Cookie");
    Header("Content-type: text/html;charset=$charset");
    Header("Content-language: $lang");
}

function manualHeader($title,$id="") {
    global $HTDIG, $LANGUAGES, $LANG, $SIDEBAR_DATA, $dbh;

    makeBorderTOC($title);

    /**
     * Show link to the package info file?
     */
    if (strstr(basename($_SERVER['PHP_SELF']), "packages.")
        && substr_count($_SERVER['PHP_SELF'], ".") > 2) {

        $package = substr(basename($_SERVER['PHP_SELF']), 0, (strlen(basename($_SERVER['PHP_SELF'])) - 4));
        $package = preg_replace("/(.*)\./", "", $package);

        $query = "SELECT id FROM packages WHERE LCASE(name) = LCASE('" . $package . "')";
        $sth = $dbh->query($query);
        $row = $sth->fetchRow();

        if (is_array($row)) {           
            ob_start();

            echo "<div align=\"center\"><br /><br />\n";

            $bb = new Borderbox("Download");
        
            echo "<div align=\"left\">\n";
            print_link("/package-info.php?pacid=" . $row[0], make_image("box-0.gif") . " Package info");
            echo "</div>\n";
            $bb->end();

            echo "</div>\n";
        
            $SIDEBAR_DATA .= ob_get_contents();
            ob_end_clean();
        }
    }

    commonHeader('Manual: '.$title);
        # create links to plain html and other languages
    if (!$HTDIG) {
        navigationBar($title, $id, "top");
    }
}

function manualFooter($title,$id="") {
    global $HTDIG;
    if (!$HTDIG) {
        navigationBar($title, $id, "bottom");
    }

    commonFooter();
}
?>
