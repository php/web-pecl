<?php

PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, "error_handler");

require_once 'layout.php';

$GLOBALS['main_menu'] = array(
    '/index.php'           => 'Home',
    '/manual/index.php'    => 'Documentation',
    '/faq.php'             => 'PEAR FAQ',
    '/packages.php'        => 'Package Browser',
    '/account-request.php' => 'Request Account'
);

$GLOBALS['user_menu'] = array(
    '/accounts.php'        => 'Account Browser',
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
    global $PHP_SELF;
    $html = "<br />\n";
    if (!empty($menu_title)) {
        $html .= "<b>$menu_title</b>\n";
        $html .= "<br />\n";
    }

    foreach ($data as $url => $tit) {
        $tt = str_replace(" ", "&nbsp;", $tit);
        if ($url == $PHP_SELF) {
            $html .= "&nbsp;&nbsp;&nbsp;" . make_image("box-1.gif") . "<b>$tt</b><br />\n";
        } else {
            $html .= "&nbsp;&nbsp;&nbsp;" . make_image("box-0.gif") . "<a href=\"$url\">$tt</a><br />\n";
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
		print "$i     <th bgcolor=\"#cccccc\">$heading</th>\n";
		for ($j = 0; $j < $this->cols-1; $j++) {
			print "$i     <td bgcolor=\"#e8e8e8\">";
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
			print "$i     <th bgcolor=\"#ffffff\">";
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
			print "$i     <td bgcolor=\"#ffffff\">";
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

function html_table_border(&$tableobj, $width = "100%")
{
    $border = new HTML_Table('border="0" cellpadding="0" cellspacing="1" '.
                             "width=\"{$width}\"");
    $border->addRow(array($tableobj->toHtml()), 'bgcolor="#000000"');
    print $border->toHtml();
}

/**
* prints "urhere" menu bar
* Top Level :: XML :: XML_RPC
* @param bool $link_lastest If the last category should or not be a link
*/
function html_category_urhere($id, $link_lastest = false)
{
    global $PHP_SELF;
    $html = "<a href=\"packages.php\">Top Level</a>";
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

function localRedirect($file)
{
    $location = "http://" . $GLOBALS['HTTP_SERVER_VARS']['HTTP_HOST'] . "/" . $file;
    header("Location: " . $location);
}

function displayed_user_email($user)
{
	return "<a href=\"mailto:$user@php.net\">$user@php.net</a>";
}

function display_user_notes($user, $width = "50%")
{
	global $dbh, $PHP_AUTH_USER;
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

?>
