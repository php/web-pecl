<?php

require_once 'layout.php';

$GLOBALS['main_menu'] = array(
    "index.php" => "Home",
    "http://php.net/manual/en/pear.php" => "Documentation",
    "account-request.php" => "Request Account",
    "package-new.php" => "New Package",
    "admin.php" => "Administrators",
    "packages.php" => "Browse Packages",
    "release-upload.php" => "Upload Release",
);

$GLOBALS['_style'] = '';

function smarty_func_page_start($params)
{
    extract($params);
    if (empty($title)) {
	$title = "The PHP Extension and Application Repository";
    }
    if (isset($sidebardata)) {
	$GLOBALS['SIDEBAR_DATA'] = $sidebardata;
    }
    response_header($title);
}

function smarty_func_page_end($params)
{
    response_footer();
}

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
	global $main_menu, $PHP_SELF;
	$SIDEBAR_DATA .= "<br /><br />\n";
	$me = basename($PHP_SELF);
	foreach ($main_menu as $url => $tit) {
	    $tt = str_replace(" ", "&nbsp;", $tit);
	    if ($url == $me) {
		$SIDEBAR_DATA .= "<b>&gt;&gt;$tt&lt;&lt;</b><br />\n";
	    } else {
		$SIDEBAR_DATA .= "&nbsp;&nbsp;&nbsp;<a href=\"$url\">$tt</a><br />\n";
	    }
	}
	$SIDEBAR_DATA .= "<br /><br />\n";
    }

    commonHeader($title);
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

function smarty_func_border_box_start($params)
{
    extract($params);
    if (!isset($width)) {
	$width = "90%";
    }
    if (!isset($indent)) {
	$indent = "";
    }
    print "$indent<table cellpadding=\"0\" cellspacing=\"1\" border=\"0\" width=\"$width\">\n";
    print "$indent <tr>\n";
    print "$indent  <td bgcolor=\"#000000\">\n";
    print "   <table cellpadding=\"2\" cellspacing=\"1\" border=\"0\" width=\"100%\">\n";
    if (isset($title)) {
	print "$indent    <tr bgcolor=\"#cccccc\">\n";
	print "$indent     <th>$title</th>\n";
	print "$indent    </tr>\n";
    }
    print "$indent    <tr bgcolor=\"#ffffff\">\n";
    print "$indent     <td>\n";
}

function smarty_func_border_box_end($params)
{
    extract($params);
    if (!isset($indent)) {
	$indent = "";
    }
    print "$indent     </td>\n";
    print "$indent    </tr>\n";
    print "$indent   </table>\n";
    print "$indent  </td>\n";
    print "$indent </tr>\n";
    print "$indent</table>\n";
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

?>
