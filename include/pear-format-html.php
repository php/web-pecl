<?php

require_once 'layout.php';

$GLOBALS['_style'] = '';

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

?>
