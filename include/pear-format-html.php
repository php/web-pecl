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

/* Send charset */
header("Content-Type: text/html; charset=utf-8");

PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, "error_handler");

$extra_styles = [];

require_once 'Net/URL2.php';
require_once __DIR__.'/layout.php';
require_once __DIR__.'/../src/BorderBox.php';

$GLOBALS['main_menu'] = [
    '/index.php'           => 'Home',
    '/news/'               => 'News'
];

$GLOBALS['docu_menu'] = [
    '/support.php'         => 'Support'
];

$GLOBALS['downloads_menu'] = [
    '/packages.php'        => 'Browse Packages',
    '/package-search.php'  => 'Search Packages',
    '/package-stats.php'   => 'Download Statistics'
];

$GLOBALS['developer_menu'] = [
    '/accounts.php'        => 'Account Browser',
    '/release-upload.php'  => 'Upload Release',
    '/package-new.php'     => 'New Package'
];

$GLOBALS['admin_menu'] = [
    '/admin/'                     => 'Overview',
    '/admin/package-maintainers.php' => 'Maintainers',
    '/admin/category-manager.php' => 'Categories'
];

$GLOBALS['_style'] = '';

function response_header($title = 'The PHP Extension Community Library', $style = false)
{
    global $_style, $_header_done, $SIDEBAR_DATA, $extra_styles, $auth_user;
    if ($_header_done) {
        return;
    }

    $_header_done = true;
    $_style       = $style;
    $rts          = rtrim($SIDEBAR_DATA);

    if (substr($rts, -1) == '-') {
        $SIDEBAR_DATA = substr($rts, 0, -1);
    } else {
        global $main_menu, $docu_menu, $downloads_menu;
        $SIDEBAR_DATA .= draw_navigation($main_menu);
        $SIDEBAR_DATA .= draw_navigation($docu_menu, 'Documentation:');
        $SIDEBAR_DATA .= draw_navigation($downloads_menu, 'Downloads:');
        if (!$GLOBALS['_NODB']) {
            init_auth_user();
        } else {
            $auth_user = null;
        }
        if (is_logged_in()) {
            global $developer_menu;
            $SIDEBAR_DATA .= draw_navigation($developer_menu, 'Developers:');
            if (auth_check(true)) {
                global $admin_menu;
                $SIDEBAR_DATA .= draw_navigation($admin_menu, 'Administrators:');
            }
        }
    }

echo '<?xml version="1.0" encoding="ISO-8859-1" ?>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
 <title>PECL :: <?php echo $title; ?></title>
 <link rel="shortcut icon" href="/gifs/pecl-favicon.ico" />
 <link rel="stylesheet" href="/css/style.css" />
<?php
    foreach ($extra_styles as $style_file) {
        echo ' <link rel="stylesheet" href="' . $style_file . "\" />\n";
    }
?>
 <link rel="alternate" type="application/rss+xml" title="RSS feed" href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/feeds/latest.rss" />
</head>

<body <?php
    if (!empty($GLOBALS['ONLOAD'])) {
        print "onload=\"" . $GLOBALS['ONLOAD']. "\"";
    }
?>
>
<div>
 <a id="TOP"></a>
</div>

<!-- START HEADER -->

<table class="head" cellspacing="0" cellpadding="0" width="100%">
 <tr>
  <td class="head-logo">
    <a href="/"><?php echo make_image('peclsmall.gif', 'PECL :: The PHP Extension Community Library', false, false, false, false, 'margin: 5px;'); ?></a><br />
  </td>

  <td class="head-menu">
      <?php

    if (empty($auth_user)) {
        echo '<a href="/login.php" class="menuBlack">Login</a>';
    } else {
        print '<small class="menuWhite">';
        print 'Logged in as ' . strtoupper($auth_user->handle) . ' (';
        print '<a class="menuWhite" href="/user/' . $auth_user->handle . '">Info</a> | ';
        print '<a class="menuWhite" href="/account-edit.php?handle=' . $auth_user->handle . '">Profile</a> | ';
        print '<a class="menuWhite" href="https://bugs.php.net/search.php?cmd=display&amp;status=Open&amp;assign=' . $auth_user->handle . '">Bugs</a>';
        print ")</small><br />\n";
        echo '<a href="/?logout=1" class="menuBlack">Logout</a>';
    }
    echo '&nbsp;|&nbsp;';
    echo '<a href="/packages.php" class="menuBlack">Packages</a>';
    echo '&nbsp;|&nbsp;';
    echo '<a href="/support.php" class="menuBlack">Support</a>';
    echo '&nbsp;|&nbsp;';
    echo '<a href="/bugs/" class="menuBlack">Bugs</a>';
      ?>
  </td>
 </tr>

 <tr>
  <td class="head-search" colspan="2">
   <form method="post" action="/search.php">
    <p class="head-search"><span class="accesskey">S</span>earch for
    <input class="small" type="text" name="search_string" value="" size="20" accesskey="s" />
    in the
    <select name="search_in" class="small">
     <option value="packages">Packages</option>
     <option value="site">This site (using Google)</option>
     <option value="developers">Developers</option>
     <option value="pecl-dev">Developer mailing list</option>
     <option value="pecl-cvs">SVN commits mailing list</option>
    </select>
    <input type="image" src="/gifs/small_submit_white.gif" alt="search" style="vertical-align: middle;" />&nbsp;<br />
    </p>
   </form>
  </td>
 </tr>
</table>

<!-- END HEADER -->
<!-- START MIDDLE -->

<table class="middle" cellspacing="0" cellpadding="0">
 <tr>

    <?php

    if (isset($SIDEBAR_DATA)) {
        ?>

<!-- START LEFT SIDEBAR -->
  <td class="sidebar_left">
   <?php echo $SIDEBAR_DATA ?>
  </td>
<!-- END LEFT SIDEBAR -->

        <?php
    }

    ?>

<!-- START MAIN CONTENT -->

  <td class="content">

    <?php
}

function response_footer($style = false)
{
    global $LAST_UPDATED, $SCRIPT_NAME, $RSIDEBAR_DATA;

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

<!-- END MAIN CONTENT -->

    <?php

    if (isset($RSIDEBAR_DATA)) {
        ?>

<!-- START RIGHT SIDEBAR -->
  <td class="sidebar_right">
   <?php echo $RSIDEBAR_DATA; ?>
  </td>
<!-- END RIGHT SIDEBAR -->

        <?php
    }

    ?>

 </tr>
</table>

<!-- END MIDDLE -->
<!-- START FOOTER -->

<table class="foot" cellspacing="0" cellpadding="0">
 <tr>
  <td class="foot-bar" colspan="2">
    <a href="/about/privacy.php" class="menuBlack">PRIVACY POLICY</a>
    &nbsp;|&nbsp;
    <a href="/credits.php" class="menuBlack">CREDITS</a>
   <br />
  </td>
 </tr>

 <tr>
  <td class="foot-copy">
   <small>
     <a href="/copyright.php">Copyright &copy; 2001-<?= date('Y'); ?> The PHP Group</a><br />
     All rights reserved.<br />
   </small>
  </td>
  <td class="foot-source">
   <small>
    Last updated: <?php echo $LAST_UPDATED; ?><br />
    Bandwidth and hardware provided by: <a href="https://www.pair.com/">pair Networks</a>
   </small>
  </td>
 </tr>
</table>

<!-- END FOOTER -->

</body>
</html>
<?php
}

function &draw_navigation($data, $menu_title = '')
{
    $html = "\n";
    if (!empty($menu_title)) {
        $html .= "<strong>$menu_title</strong>\n";
    }

    $html .= '<ul class="side_pages">' . "\n";
    foreach ($data as $url => $tit) {
        $html .= ' <li class="side_page">';
        if ($url == $_SERVER['PHP_SELF']) {
            $html .= '<strong>' . $tit . '</strong>';
        } else {
            $html .= '<a href="' . $url . '">' . $tit . '</a>';
        }
        $html .= "</li>\n";
    }
    $html .= "</ul>\n\n";

    return $html;
}

function menu_link($text, $url) {
    echo "<p>\n";
    echo '<a href="'.$url.'">'.make_image('pecl_item.gif', $text).'</a>';
    echo '&nbsp;';
    echo '<a href="'.$url.'"><b>'.$text.'</b></a>';
    echo "</p>\n";
}

/**
 * Display errors or warnings as a <ul> inside a <div>
 *
 * Here's what happens depending on $in:
 *   + string: value is printed
 *   + array:  looped through and each value is printed.
 *             If array is empty, nothing is displayed.
 *             If a value contains a PEAR_Error object,
 *   + PEAR_Error: prints the value of getMessage() and getUserInfo()
 *                 if DEVBOX is true, otherwise prints data from getMessage().
 *
 * @param string|array|PEAR_Error $in  see long description
 * @param string $class  name of the HTML class for the <div> tag.
 *                        ("errors", "warnings")
 * @param string $head   string to be put above the message
 *
 * @return bool  true if errors were submitted, false if not
 */
function report_error($in, $class = 'errors', $head = 'ERROR:')
{
    if (PEAR::isError($in)) {
        if (DEVBOX == true) {
            $in = [$in->getMessage() . '... ' . $in->getUserInfo()];
        } else {
            $in = [$in->getMessage()];
        }
    } elseif (!is_array($in)) {
        $in = [$in];
    } elseif (!count($in)) {
        return false;
    }

    echo '<div class="' . $class . '">' . $head . '<ul>';
    foreach ($in as $msg) {
        if (PEAR::isError($msg)) {
            if (DEVBOX == true) {
                $msg = $msg->getMessage() . '... ' . $msg->getUserInfo();
            } else {
                $msg = $msg->getMessage();
            }
        }
        echo '<li>' . htmlspecialchars($msg) . "</li>\n";
    }
    echo "</ul></div>\n";
    return true;
}

/**
 * Forwards warnings to report_error()
 *
 * For use with PEAR_ERROR_CALLBACK to get messages to be formatted
 * as warnings rather than errors.
 *
 * @param string|array|PEAR_Error $in  see report_error() for more info
 *
 * @return bool  true if errors were submitted, false if not
 *
 * @see report_error()
 */
function report_warning($in)
{
    return report_error($in, 'warnings', 'WARNING:');
}

/**
 * Generates a complete PEAR web page with an error message in it then
 * calls exit
 *
 * For use with PEAR_ERROR_CALLBACK error handling mode to print fatal
 * errors and die.
 *
 * @param string|array|PEAR_Error $in  see report_error() for more info
 * @param string $title  string to be put above the message
 *
 * @return void
 *
 * @see report_error()
 */
function error_handler($errobj, $title = 'Error')
{
    response_header($title);
    report_error($errobj);
    response_footer();
    exit;
}

/**
 * Displays success messages inside a <div>
 *
 * @param string $in  the message to be displayed
 *
 * @return void
 */
function report_success($in)
{
    echo '<div class="success">';
    echo htmlspecialchars($in);
    echo "</div>\n";
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
* Returns an absolute URL using Net_URL2
*
* @param  string $url All/part of a url
* @return string      Full url
*/
function getURL($url)
{
    $obj = new Net_URL2($url);
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

        case 'PHP License':
        case 'PHP 3.01':
        case 'PHP License 3.01':
            $link = 'https://php.net/license/3_01.txt';
            break;

        case 'PHP 3.0':
        case 'PHP License 3.0':
            $link = 'https://php.net/license/3_0.txt';
            break;

        case 'PHP 2.02':
        case 'PHP License 2.02':
            $link = 'https://php.net/license/2_02.txt';
            break;

        case 'LGPL':
        case 'GNU Lesser General Public License':
            $link = 'https://www.gnu.org/licenses/lgpl.html';
            break;

        default:
            $link = '';
            break;
    }

    return ($link != '' ? '<a href="' . $link . '">' . $license . "</a>\n" : $license);
}

function display_user_notes($user, $width = '50%')
{
    global $dbh;
    $bb = new BorderBox("Notes for user $user", $width);
    $notes = $dbh->getAssoc("SELECT id,nby,ntime,note FROM notes ".
                "WHERE uid = ? ORDER BY ntime", true, [$user]);
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
