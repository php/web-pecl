<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2003 The PHP Group                                |
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

/* Send charset */
header("Content-Type: text/html; charset=iso-8859-1");

PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, "error_handler");

require_once 'layout.php';

$GLOBALS['main_menu'] = array(
    '/index.php'           => 'Home',
    '/news/'               => 'News'
);

$GLOBALS['docu_menu'] = array(
    '/support.php'         => 'Support'
);

$GLOBALS['downloads_menu'] = array(
    '/packages.php'        => 'Browse Packages',
    '/package-search.php'  => 'Search Packages',
    '/package-stats.php'   => 'Download Statistics'
);

$GLOBALS['developer_menu'] = array(
    '/accounts.php'        => 'Account Browser',
    '/release-upload.php'  => 'Upload Release',
    '/package-new.php'     => 'New Package'
);

$GLOBALS['admin_menu'] = array(
    '/admin/'                     => 'Overview',
    '/admin/package-maintainers.php' => 'Maintainers',
    '/admin/category-manager.php' => 'Categories'
);

$GLOBALS['_style'] = '';

function response_header($title = 'The PHP Extension Community Library', $style = false)
{
    global $_style, $_header_done, $SIDEBAR_DATA;
    if ($_header_done) {
        return;
    }
    $_header_done = true;
    $_style = $style;
    $rts = rtrim($SIDEBAR_DATA);
    if (substr($rts, -1) == '-') {
        $SIDEBAR_DATA = substr($rts, 0, -1);
    } else {
        global $main_menu, $docu_menu, $downloads_menu, $auth_user;
        $SIDEBAR_DATA .= draw_navigation($main_menu);
        $SIDEBAR_DATA .= draw_navigation($docu_menu, 'Documentation:');
        $SIDEBAR_DATA .= draw_navigation($downloads_menu, 'Downloads:');
        init_auth_user();
        if (!empty($auth_user)) {
            if (!empty($auth_user->registered)) {
                global $developer_menu;
                $SIDEBAR_DATA .= draw_navigation($developer_menu, 'Developers:');
            }
            if (!empty($auth_user->admin)) {
                global $admin_menu;
                $SIDEBAR_DATA .= draw_navigation($admin_menu, 'Administrators:');
            }
        }
    }
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
 <title>PECL :: <?php echo $title; ?></title>
 <link rel="shortcut icon" href="/gifs/pecl-favicon.ico" />
 <link rel="stylesheet" href="/style.css" />
 <link rel="alternate" type="application/rss+xml" title="RSS feed" href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/feeds/latest.rss" />
</head>

<body <?php
    if (!empty($GLOBALS['ONLOAD'])) {
        print "onload=\"" . $GLOBALS['ONLOAD']. "\"";
    }
?>
        bgcolor="#ffffff"
        text="#000000"
        link="#000066"
        alink="#cc00cc"
        vlink="#000033"
><a name="TOP" />
<table border="0" cellspacing="0" cellpadding="0" width="100%">
  <tr bgcolor="#330099">
    <td align="left" rowspan="2" width="120" colspan="2" height="1">
<?php print_link('/', make_image('peclsmall.gif', 'PECL', false, 'vspace="5" hspace="5"') ); ?><br />
    </td>
    <td align="right" valign="top" colspan="3" height="1">
      <font color="#ffffff"><b>
        <?php echo strftime("%A, %B %d, %Y"); ?>
      </b>&nbsp;<br />
     </font>
    </td>
  </tr>

  <tr bgcolor="#330099">
    <td align="right" valign="bottom" colspan="3" height="1">
      <?php

    if (empty($_COOKIE['PEAR_USER'])) {
        print_link('/login.php', 'Login', false, 'class="menuLink"');
    } else {
        print '<span class="menuWhite"><small>logged in as ';
        print strtoupper($_COOKIE['PEAR_USER']);
        print '&nbsp;</small></span><br />';
        print_link('/?logout=1', 'Logout', false, 'class="menuLink"');
    }
    echo delim();
    print_link('/packages.php', 'Packages', false, 'class="menuLink"');
    echo delim();
    print_link('/support.php','Support',false,'class="menuLink"');
    echo delim();
    print_link('/bugs/','Bugs',false,'class="menuLink"');
      ?>&nbsp;<br />
      <?php spacer(2,2); ?><br />
    </td>
  </tr>

  <tr bgcolor="#000033"><td colspan="5" height="1"><?php spacer(1,1);?><br /></td></tr>

  <tr bgcolor="#000066">
    <td align="right" valign="top" colspan="5" height="1" class="menuWhite">
    <form method="post" action="/search.php">
    <small>Search for</small>
    <input class="small" type="text" name="search_string" value="" size="20" />
    <small>in the</small>
    <select name="search_in" class="small">
	<option value="packages">Packages</option>
    <option value="pear-dev">Developer mailing list</option>
    <option value="pear-general">General mailing list</option>
    <option value="pear-cvs">CVS commits mailing list</option>
    </select>
    <input type="image" src="/gifs/small_submit_white.gif" alt="search" align="bottom" />&nbsp;<br /></form></td></tr>

  <tr bgcolor="#000033"><td colspan="5" height="1"><?php spacer(1,1);?><br /></td></tr>

  <!-- Middle section -->

 <tr valign="top">
<?php if (isset($SIDEBAR_DATA)) { ?>
  <td colspan="2" class="sidebar_left" bgcolor="#f0f0f0" width="149">
   <table width="149" cellpadding="4" cellspacing="0">
    <tr valign="top">
     <td><?php echo $SIDEBAR_DATA?><br /></td>
    </tr>
   </table>
  </td>
<?php } ?>
  <td>
   <table width="100%" cellpadding="10" cellspacing="0">
    <tr>
     <td valign="top">
<?php
}

function &draw_navigation($data, $menu_title='')
{
    $html = "<br />\n";
    if (!empty($menu_title)) {
        $html .= "<b>$menu_title</b>\n";
        $html .= "<br />\n";
    }

    foreach ($data as $url => $tit) {
        $tt = str_replace(" ", "&nbsp;", $tit);
        if ($url == $_SERVER['PHP_SELF']) {
            $html .= make_image("box-1.gif") . "<b>$tt</b><br />\n";
        } else {
            $html .= make_image("box-0.gif") . "<a href=\"$url\">$tt</a><br />\n";
        }
    }
    return $html;
}

function response_footer($style = false)
{
    global $LAST_UPDATED, $MIRRORS, $MYSITE, $COUNTRIES,$SCRIPT_NAME, $RSIDEBAR_DATA;

    static $called;
    if ($called) {
        return;
    }
    $called = true;
    if (!$style) {
        $style = $GLOBALS['_style'];
    }


?>
     </td>
    </tr>
   </table>
  </td>

<?php if (isset($RSIDEBAR_DATA)) { ?>
  <td class="sidebar_right" width="149" bgcolor="#f0f0f0">
    <table width="149" cellpadding="4" cellspacing="0">
     <tr valign="top">
      <td><?php echo $RSIDEBAR_DATA; ?><br />
     </td>
    </tr>
   </table>
  </td>
<?php } ?>

 </tr>

 <!-- Lower bar -->

  <tr bgcolor="#000033"><td colspan="5" height="1"><?php spacer(1,1);?><br /></td></tr>
  <tr bgcolor="#330099">
      <td align="right" valign="bottom" colspan="5" height="1">
<?php
print_link('/about/privacy.php', 'PRIVACY POLICY', false, 'class="menuLink"');
echo delim();
print_link('/credits.php', 'CREDITS', false, 'class="menuLink"');
?>
      <br />
      </td>
  </tr>
  <tr bgcolor="#000033"><td colspan="5" height="1"><?php spacer(1,1); ?><br /></td></tr>

  <tr valign="top" bgcolor="#cccccc">
    <td colspan="5" height="1">
	  <table border="0" cellspacing="0" cellpadding="5" width="100%">
	  	<tr>
		 <td>
		  <small>
	      <?php print_link('/copyright.php', 'Copyright &copy; 2001-2004 The PHP Group'); ?><br />
	      All rights reserved.<br />
	      </small>
		 </td>
		 <td align="right" valign="top">
		  <small>
	      Last updated: <?php echo $LAST_UPDATED; ?><br />
	      Bandwidth and hardware provided by: <?php print_link("http://www.pair.com/", "pair Networks"); ?>
	      </small>
		 </td>
		</tr>
      </table>
    </td>
  </tr>
</table>

</body>
</html>
<?php
}

function menu_link($text, $url) {
    echo "<p>\n";
    print_link($url, make_image('pecl_item.gif', $text) );
    echo '&nbsp;';
    print_link($url, '<b>' . $text . '</b>' );
    echo "</p>\n";
}

function report_error($error)
{
    if (PEAR::isError($error)) {
        $error = $error->getMessage();
        $info = $error->getUserInfo();
        if ($info) {
            $error .= " : $info";
        }
    }
    print "<font color=\"#990000\"><b>$error</b></font><br />\n";
}

function error_handler($errobj, $title = "Error")
{
    if (PEAR::isError($errobj)) {
        $msg = $errobj->getMessage();
        $info = $errobj->getUserInfo();
    } else {
        $msg = $errobj;
        $info = '';
    }
    response_header($title);
    $report = "Error: $msg";
    if ($info) {
        $report .= ": $info";
    }
    for ($i = 0; $i < 3; $i++) {
        $report .= "</TD></TR></TABLE>";
    }
    print "<font color=\"#990000\"><b>$report</b></font><br />\n";
    response_footer();
    exit;
}


class BorderBox {
    function BorderBox($title, $width = "90%", $indent = "", $cols = 1,
                       $open = false) {
        $this->title = $title;
        $this->width = $width;
        $this->indent = $indent;
        $this->cols = $cols;
        $this->open = $open;
        $this->start();
    }

    function start() {
        $title = $this->title;
        if (is_array($title)) {
            $title = implode("</th><th>", $title);
        }
        $i = $this->indent;
        print "<!-- border box starts -->\n";
        print "$i<table cellpadding=\"0\" cellspacing=\"1\" border=\"0\" width=\"$this->width\">\n";
        print "$i <tr>\n";
        print "$i  <td bgcolor=\"#000000\">\n";
        print "$i   <table cellpadding=\"2\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
        print "$i    <tr bgcolor=\"#cccccc\">\n";
        print "$i     <th";
        if ($this->cols > 1) {
            print " colspan=\"$this->cols\"";
        }
        print ">$title</th>\n";
        print "$i    </tr>\n";
        if (!$this->open) {
            print "$i    <tr bgcolor=\"#ffffff\">\n";
            print "$i     <td>\n";
        }
    }

    function end() {
        $i = $this->indent;
        if (!$this->open) {
            print "$i     </td>\n";
            print "$i    </tr>\n";
        }
        print "$i   </table>\n";
        print "$i  </td>\n";
        print "$i </tr>\n";
        print "$i</table>\n";
        print "<!-- border box ends -->\n";
    }

    function horizHeadRow($heading /* ... */) {
        $i = $this->indent;
        print "$i    <tr>\n";
        print "$i     <th valign=\"top\" bgcolor=\"#cccccc\">$heading</th>\n";
        for ($j = 0; $j < $this->cols-1; $j++) {
            print "$i     <td valign=\"top\" bgcolor=\"#e8e8e8\">";
            $data = @func_get_arg($j + 1);
            if (empty($data)) {
                print "&nbsp;";
            } else {
                print $data;
            }
            print "</td>\n";
        }
        print "$i    </tr>\n";

    }

    function headRow() {
        $i = $this->indent;
        print "$i    <tr>\n";
        for ($j = 0; $j < $this->cols; $j++) {
            print "$i     <th valign=\"top\" bgcolor=\"#ffffff\">";
            $data = @func_get_arg($j);
            if (empty($data)) {
                print "&nbsp;";
            } else {
                print $data;
            }
            print "</th>\n";
        }
        print "$i    </tr>\n";
    }

    function plainRow(/* ... */) {
        $i = $this->indent;
        print "$i    <tr>\n";
        for ($j = 0; $j < $this->cols; $j++) {
            print "$i     <td valign=\"top\" bgcolor=\"#ffffff\">";
            $data = @func_get_arg($j);
            if (empty($data)) {
                print "&nbsp;";
            } else {
                print $data;
            }
            print "</td>\n";
        }
        print "$i    </tr>\n";
    }

    function fullRow($text) {
        $i = $this->indent;
        print "$i    <tr>\n";
        print "$i     <td bgcolor=\"#e8e8e8\"";
        if ($this->cols > 1) {
            print " colspan=\"$this->cols\"";
        }
        print ">$text</td>\n";
        print "$i    </tr>\n";

    }
}

/**
* prints "urhere" menu bar
* Top Level :: XML :: XML_RPC
* @param bool $link_lastest If the last category should or not be a link
*/
function html_category_urhere($id, $link_lastest = false)
{
    $html = "<a href=\"/packages.php\">Top Level</a>";
    if ($id !== null) {
        global $dbh;
        $res = $dbh->query("SELECT c.id, c.name
                            FROM categories c, categories cat
                            WHERE cat.id = $id
                            AND c.cat_left <= cat.cat_left
                            AND c.cat_right >= cat.cat_right");
        $nrows = $res->numRows();
        $i = 0;
        while ($res->fetchInto($row, DB_FETCHMODE_ASSOC)) {
            if (!$link_lastest && $i >= $nrows -1) {
                break;
            }
            $html .= "  :: ".
                     "<a href=\"/packages.php?catpid={$row['id']}&catname={$row['name']}\">".
                     "{$row['name']}</a>";
            $i++;
        }
        if (!$link_lastest) {
            $html .= "  :: <b>".$row['name']."</b>";
        }
    }
    print $html;
}

/**
* Returns an absolute URL using Net_URL
*
* @param  string $url All/part of a url
* @return string      Full url
*/
function getURL($url)
{
	include_once('Net/URL.php');
	$obj = new Net_URL($url);
	return $obj->getURL();
}

/**
* Redirects to the given full or partial URL.
* will turn the given url into an absolute url
* using the above getURL() function. This function
* does not return.
*
* @param string $url Full/partial url to redirect to
*/
function localRedirect($url)
{
	header('Location: ' . getURL($url));
	exit;
}

/**
 * Get URL to license text
 *
 * @todo  Add more licenses here
 * @param string Name of the license
 * @return string Link to license URL
 */
function get_license_link($license = "")
{
    switch ($license) {

        case "PHP License" :
        case "PHP 2.02" :
            $link = "http://www.php.net/license/2_02.txt";
            break;

        case "GPL" :
        case "GNU General Public License" :
            $link = "http://www.gnu.org/licenses/gpl.html";
            break;

        case "LGPL" :
        case "GNU Lesser General Public License" :
            $link = "http://www.gnu.org/licenses/lgpl.html";
            break;

        default :
            $link = "";
            break;
    }

    return ($link != "" ? "<a href=\"" . $link . "\">" . $license . "</a>\n" : $license);
}

function display_user_notes($user, $width = "50%")
{
    global $dbh;
    $bb = new BorderBox("Notes for user $user", $width);
    $notes = $dbh->getAssoc("SELECT id,nby,ntime,note FROM notes ".
                "WHERE uid = ? ORDER BY ntime", true, array($user));
    if (!empty($notes)) {
        print "<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\">\n";
        foreach ($notes as $nid => $data) {
        print " <tr>\n";
        print "  <td>\n";
        print "   <b>{$data['nby']} {$data['ntime']}:</b>";
        print "<br />\n";
        print "   ".htmlspecialchars($data['note'])."\n";
        print "  </td>\n";
        print " </tr>\n";
        print " <tr><td>&nbsp;</td></tr>\n";
        }
        print "</table>\n";
    } else {
        print "No notes.";
    }
    $bb->end();
    return sizeof($notes);
}

// {{{ user_link()

/**
 * Create link to the account information page and to the user's wishlist
 *
 * @param string User's handle
 * @param bool   Should the wishlist link be skipped?
 * @return mixed False on error, otherwise string
 */
function user_link($handle, $compact = false)
{
    global $dbh;

    $query = "SELECT name, wishlist FROM users WHERE handle = '" . $handle . "'";
    $row = $dbh->getRow($query, DB_FETCHMODE_ASSOC);

    if (!is_array($row)) {
        return false;
    }

    return sprintf("<a href=\"/user/%s\">%s</a>%s\n",
                   $handle,
                   $row['name'],
                   ($row['wishlist'] != "" && $compact == false ? " [<a href=\"" . htmlentities($row['wishlist']) . "\">Wishlist</a>]" : "")
                   );
}

// }}}
?>
