<?php

require_once 'layout.php';

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
    global $_style, $_header_done;
    if ($_header_done) {
        return;
    }
    $_header_done = true;
    $_style = $style;

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
    echo "<P>\n";
    print_link($url, make_image('pear_item.gif', $text) );
    echo '&nbsp;';
    print_link($url, '<B>' . $text . '</B>' );
    echo "</P>\n";
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
    print "<FONT COLOR=\"#990000\"><B>$error</B></FONT><BR>\n";
}

function error_handler($errobj)
{
    if (PEAR::isError($errobj)) {
        $msg = $errobj->getMessage();
        $info = $errobj->getUserInfo();
    } else {
        $msg = $errobj;
        $info = '';
    }
    response_header("Error");
    $report = "Error: $msg";
    if ($info) {
        $report .= ": $info";
    }
    for ($i = 0; $i < 3; $i++) {
        $report .= "</TD></TR></TABLE>";
    }
    print "<FONT COLOR=\"#990000\"><B>$report</B></FONT><BR>\n";
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

?>
