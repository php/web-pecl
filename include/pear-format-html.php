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
    '/manual/index.php'    => 'Documentation',
    '/manual/en/faq.php'   => 'PEAR FAQ',
    '/packages.php'        => 'Package Browser',
    '/package-search.php'  => 'Package Search',
	'/package-stats.php'   => 'Package Statistics',
    '/account-request.php' => 'Request Account'
);

$GLOBALS['user_menu'] = array(
    '/accounts.php'        => 'Account Browser',
    '/package-new.php'     => 'New Package',
    '/release-upload.php'  => 'Upload Release'
);

$GLOBALS['admin_menu'] = array(
    '/admin/'                     => 'Overview',
    '/admin/category-manager.php' => 'Categories',
    '/admin/package-maintainers.php' => 'Maintainers'
);

$GLOBALS['_style'] = '';

function response_header($title = 'The PHP Extension and Application Repository', $style = false)
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
        global $main_menu, $auth_user;
        $SIDEBAR_DATA .= draw_navigation($main_menu);
        init_auth_user();
        if (!empty($auth_user)) {
            if (!empty($auth_user->registered)) {
                global $user_menu;
                $SIDEBAR_DATA .= draw_navigation($user_menu, 'Developers:');
            }
            if (!empty($auth_user->admin)) {
                global $admin_menu;
                $SIDEBAR_DATA .= draw_navigation($admin_menu, 'Administrators:');
            }
        }
    }
    commonHeader($title);
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
    static $called;
    if ($called) {
        return;
    }
    $called = true;
    if (!$style) {
        $style = $GLOBALS['_style'];
    }

    commonFooter();

}

function menu_link($text, $url) {
    echo "<p>\n";
    print_link($url, make_image('pear_item.gif', $text) );
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
        list($nby, $ntime, $note) = $data;
        print " <tr>\n";
        print "  <td>\n";
        print "   <b>$nby $ntime:</b>";
        print "<br />\n";
        print "   ".htmlspecialchars($note)."\n";
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
 * @return mixed False on error, otherwise string
 */
function user_link($handle)
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
                   ($row['wishlist'] != "" ? " [<a href=\"" . htmlentities($row['wishlist']) . "\">Wishlist</a>]" : "")
                   );
}

// }}}
?>
