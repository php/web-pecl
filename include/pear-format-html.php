<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2006 The PHP Group                                |
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
header("Content-Type: text/html; charset=ISO-8859-1");

PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, "error_handler");

$extra_styles = array();

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
    global $_style, $_header_done, $SIDEBAR_DATA, $extra_styles;
    if ($_header_done) {
        return;
    }

    $_header_done = true;
    $_style       = $style;
    $rts          = rtrim($SIDEBAR_DATA);

    if (substr($rts, -1) == '-') {
        $SIDEBAR_DATA = substr($rts, 0, -1);
    } else {
        global $main_menu, $docu_menu, $downloads_menu, $auth_user;
        $SIDEBAR_DATA .= draw_navigation($main_menu);
        $SIDEBAR_DATA .= draw_navigation($docu_menu, 'Documentation:');
        $SIDEBAR_DATA .= draw_navigation($downloads_menu, 'Downloads:');
        if (!$GLOBALS['_NODB']) {
            init_auth_user();
        } else {
            $auth_user = null;
        }
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
<?php print_link('/', make_image('peclsmall.gif', 'PECL :: The PHP Extension Community Library', false, false, false, false, 'margin: 5px;')); ?><br />
  </td>

  <td class="head-menu">
      <?php

    if (empty($_COOKIE['PEAR_USER'])) {
        print_link('/login.php', 'Login', false, 'class="menuBlack"');
    } else {
        print '<small class="menuWhite">';
        print 'Logged in as ' . strtoupper($_COOKIE['PEAR_USER']) . ' (';
        print '<a class="menuWhite" href="/user/' . $_COOKIE['PEAR_USER'] . '">Info</a> | ';
        print '<a class="menuWhite" href="/account-edit.php?handle=' . $_COOKIE['PEAR_USER'] . '">Profile</a> | ';
        print '<a class="menuWhite" href="/bugs/search.php?handle=' . $_COOKIE['PEAR_USER'] . '&amp;cmd=display">Bugs</a>';
        print ")</small><br />\n";
        print_link('/?logout=1', 'Logout', false, 'class="menuBlack"');
    }
    echo delim();
    print_link('/packages.php', 'Packages', false, 'class="menuBlack"');
    echo delim();
    print_link('/support.php', 'Support', false, 'class="menuBlack"');
    echo delim();
    print_link('/bugs/', 'Bugs', false, 'class="menuBlack"');
      ?>&nbsp;<br />
      <?php spacer(2,2); ?><br />
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
     <option value="pear-dev">Developer mailing list</option>
     <option value="pear-cvs">CVS commits mailing list</option>
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
<?php
print_link('/about/privacy.php', 'PRIVACY POLICY', false, 'class="menuBlack"');
echo delim();
print_link('/credits.php', 'CREDITS', false, 'class="menuBlack"');
?>
   <br />
  </td>
 </tr>

 <tr>
  <td class="foot-copy">
   <small>
	<?php print_link('/copyright.php', 'Copyright &copy; 2001-2008 The PHP Group'); ?><br />
     All rights reserved.<br />
   </small>
  </td>
  <td class="foot-source">
   <small>
    Last updated: <?php echo $LAST_UPDATED; ?><br />
    Bandwidth and hardware provided by: <?php print_link("http://www.pair.com/", "pair Networks"); ?>
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
    print_link($url, make_image('pecl_item.gif', $text) );
    echo '&nbsp;';
    print_link($url, '<b>' . $text . '</b>' );
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
            $in = array($in->getMessage() . '... ' . $in->getUserInfo());
        } else {
            $in = array($in->getMessage());
        }
    } elseif (!is_array($in)) {
        $in = array($in);
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
            $title = implode('</th><th>', $title);
        }
        $i = $this->indent;
        print "<!-- border box starts -->\n";
        print "$i<table cellpadding=\"0\" cellspacing=\"1\" style=\"width: $this->width; border: 0px;\">\n";
        print "$i <tr>\n";
        print "$i  <td bgcolor=\"#000000\">\n";
        print "$i   <table cellpadding=\"2\" cellspacing=\"1\" style=\"width: 100%; border: 0px;\">\n";
        print "$i    <tr style=\"background-color: #CCCCCC;\">\n";
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
	include_once 'Net/URL2.php';
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
            $link = 'http://www.php.net/license/3_01.txt';
            break;

        case 'PHP 3.0':
        case 'PHP License 3.0':
            $link = 'http://www.php.net/license/3_0.txt';
            break;
	
        case 'PHP 2.02':
        case 'PHP License 2.02':
            $link = 'http://www.php.net/license/2_02.txt';
            break;

        case 'LGPL':
        case 'GNU Lesser General Public License':
            $link = 'http://www.gnu.org/licenses/lgpl.html';
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

    return sprintf("<a href=\"/user/%s\">%s</a>&nbsp;%s\n",
                   $handle,
                   $row['name'],
                   ($row['wishlist'] != "" && $compact == false ? '['.make_link('http://' . $_SERVER['HTTP_HOST'] . '/wishlist.php/' . $handle, 'Wishlist').']' : '')
                   );
}

// }}}

/**
 * Sets <var>$_SESSION['captcha']</var> and
 * <var>$_SESSION['captcha_time']</var> then prints the XHTML that
 * displays a CAPTCHA image and a form input element
 *
 * Only generate a new <var>$_SESSION['captcha']</var> if it doesn't exist
 * yet.  This avoids the problem of the CAPTCHA value being changed but the
 * old image remaining in the browser's cache.  This is necessary because
 * caching can not be reliably disabled.
 *
 * Use upper case letters to reduce confusion with some of these fonts.
 * Input is passed through strtoupper() before comparison.
 *
 * Don't use "I" or "O" to avoid confusion with numbers.  Don't use digits
 * because some of the fonts don't handle them.
 *
 * @return string  the CAPTCHA image and form intut
 *
 * @see validate_captcha(), captcha-image.php
 */
function generate_captcha()
{
    if (!isset($_SESSION['captcha'])) {
        $_SESSION['captcha'] = '';
        $useable = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        for ($i = 0; $i < 4; $i++) {
            $_SESSION['captcha'] .= substr($useable, mt_rand(0, 23), 1);
        }
        $_SESSION['captcha_time'] = time();
    }
    return 'Type <img src="/captcha-image.php?x=' . time()
           . '" alt="If you are unable to'
           . ' read this image, click the help link to the right of'
           . ' the input box" align="top" /> into this box...'
           . ' <input type="text" size="4" maxlength="4" name="captcha" />'
           . ' (<a href="/captcha-help.php" target="_blank">help</a>)'
           . ' <br />If this image is hard to read, reload the page.';

}

/**
 * Check if the CAPTCHA value submitted by the user in
 * <var>$_POST['captcha']</var> matches <var>$_SESSION['captcha']</var>
 * and that the submission was made within the allowed time frame
 * of the CAPTCHA being generated
 *
 * If the two values aen't the same or the length of time between CAPTCHA
 * generation and form submission is too long, this function will unset()
 * <var>$_SESSION['captcha']</var>.  Unsetting it will cause
 * generate_captcha() to come up with a new CAPTCHA value and image.
 * This prevents brute force attacks.
 *
 * Similarly, if the submission is correct <var>$_SESSION['captcha']</var>
 * is unset() in order to keep robots from making multiple requests with
 * a correctly guessed CAPTCHA value.
 *
 * @param int $max_age  the length of time in seconds since the CAPTCHA was
 *                      generated during which a submission should be
 *                      considered valid.  Default is 300 seconds
 *                      (aka 5 minutes).
 *
 * @return bool  true if input matches captcha, false if not
 *
 * @see generate_captcha(), captcha-image.php
 */
function validate_captcha($max_age = 300)
{
    if (!isset($_POST['captcha']) ||
        !isset($_SESSION['captcha']) ||
        (time() - $_SESSION['captcha_time']) > $max_age ||
        $_SESSION['captcha'] != strtoupper($_POST['captcha']))
    {
        unset($_SESSION['captcha']);
        unset($_SESSION['captcha_time']);
        return false;
    } else {
        unset($_SESSION['captcha']);
        unset($_SESSION['captcha_time']);
        return true;
    }
}

/**
 * Turns bug/feature request numbers into hyperlinks
 *
 * If the bug number is prefixed by the word "PHP," the link will
 * go to bugs.php.net.  Otherwise, the bug is considered a PECL bug.
 *
 * @param string $text  the text to check for bug numbers
 *
 * @return string  the string with bug numbers hyperlinked
 */
function make_ticket_links($text)
{
    $text = preg_replace('/(?<=php)\s*(bug|request)\s+#?([0-9]+)/i',
                         ' <a href="http://bugs.php.net/\\2">\\1 \\2</a>',
                         $text);
    $text = preg_replace('/(?<![>a-z])(bug|request)\s+#?([0-9]+)/i',
                         '<a href="/bugs/\\2">\\0</a>', $text);
    return $text;
}

?>
