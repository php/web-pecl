<?php

PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, "error_handler");

require_once 'layout.php';

$GLOBALS['main_menu'] = array(
    '/index.php'           => 'Home',
    '/manual/index.php'    => 'Documentation',
    '/faq.php'             => 'PEAR FAQ',
    '/packages.php'        => 'Package Browser',
    '/accounts.php'        => 'Account Browser',
    '/account-request.php' => 'Request Account'
);

$GLOBALS['user_menu'] = array(
    '/package-new.php'     => 'New Package',
    '/release-upload.php'  => 'Upload Release'
);

$GLOBALS['admin_menu'] = array(
    '/admin.php'            => 'Account Requests',
    '/category-manager.php' => 'Manage Categories'
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
    } elseif (DEVBOX) {  // For now only show bar on dev boxes
        global $main_menu, $auth_user;
        $SIDEBAR_DATA .= draw_navigation($main_menu);
        init_auth_user();
        if (!empty($auth_user)) {
            if (!empty($auth_user->registered)) {
                global $user_menu;
                $SIDEBAR_DATA .= draw_navigation($user_menu, 'User Actions:');
            }
            if (!empty($auth_user->admin)) {
                global $admin_menu;
                $SIDEBAR_DATA .= draw_navigation($admin_menu, 'Admin Actions:');
            }
        }
    }
    commonHeader($title);
}

function &draw_navigation($data, $menu_title='')
{
    global $PHP_SELF;
    $html = "<br /><br />\n";
    if (!empty($menu_title)) {
        $html .= "<b>$menu_title</b>\n";
        $html .= "<br /><br />\n";
    }
    $me = basename($PHP_SELF);
    foreach ($data as $url => $tit) {
        $tt = str_replace(" ", "&nbsp;", $tit);
        if ($url == $me) {
            $html .= "<b>&gt;&gt;$tt&lt;&lt;</b><br />\n";
        } else {
            $html .= "&nbsp;&nbsp;&nbsp;<a href=\"$url\">$tt</a><br />\n";
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


function border_box_start($title, $width = "90%", $indent = "")
{
    if (is_array($title)) {
        $title = implode("</th><th>", $title);
    }
    print "$indent<table cellpadding=\"0\" cellspacing=\"1\" border=\"0\" width=\"$width\">\n";
    print "$indent <tr>\n";
    print "$indent  <td bgcolor=\"#000000\">\n";
    print "   <table cellpadding=\"2\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
    print "$indent    <tr bgcolor=\"#cccccc\">\n";
    print "$indent     <th>$title</th>\n";
    print "$indent    </tr>\n";
    print "$indent    <tr bgcolor=\"#ffffff\">\n";
    print "$indent     <td>\n";
}

function border_box_end($indent = "")
{
    print "$indent     </td>\n";
    print "$indent    </tr>\n";
    print "$indent   </table>\n";
    print "$indent  </td>\n";
    print "$indent </tr>\n";
    print "$indent</table>\n";
}

function html_table_border(&$tableobj, $width = "100%")
{
    $border = new HTML_Table('border="0" cellpadding="0" cellspacing="1" '.
                             "width=\"{$width}\"");
    $border->addRow(array($tableobj->toHtml()), 'bgcolor="#000000"');
    print $border->toHtml();
}

// prints "urhere" menu bar
function html_category_urhere($id, $name=null)
{
    global $PHP_SELF;
    $html = "<a href=\"$PHP_SELF\">Top Level</a>";
    if ($id !== null) {
        global $dbh;
        $res = $dbh->query("SELECT c.id, c.name
                            FROM categories c, categories cat
                            WHERE cat.id = $id
                            AND c.cat_left < cat.cat_left
                            AND c.cat_right > cat.cat_right");

        while ($res->fetchInto($row, DB_FETCHMODE_ASSOC)) {
            $html .= "  :: ".
                     "<a href=\"$PHP_SELF?catpid={$row['id']}&catname={$row['name']}\">".
                     "{$row['name']}</a>";
        }
        $html .= "  :: <b>$name</b>";
    }
    print "$html<br /><br />";
}

function localRedirect($file)
{
    $location = "http://" . $GLOBALS['HTTP_SERVER_VARS']['HTTP_HOST'] . "/" . $file;
    header("Location: " . $location);
}
?>
