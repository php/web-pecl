<?php
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

if (empty($prevsearch)) $prevsearch = '';

// spacer()
// print a IMG tag for a sized spacer GIF
//

function spacer($width=1, $height=1, $align=false, $extras=false) {
    printf('<img src="/gifs/spacer.gif" width="%d" height="%d" border="0" alt="" %s%s />',
        $width,
        $height,
        ($align ? 'align="'.$align.'" ' : ''),
        ($extras ? $extras : '')
    );
}



// resize_image()
// tag the output of make_image() and resize it manually
//

function resize_image($img, $width=1, $height=1) {
    $str = preg_replace('/width=\"([0-9]+?)\"/i', '', $img );
    $str = preg_replace('/height=\"([0-9]+?)\"/i', '', $str );
    $str = substr($str,0,-1) . sprintf(' height="%s" width="%s" />', $height, $width );
    return $str;
}



// make_image()
// return an IMG tag for a given file (relative to the images dir)
//

function make_image($file, $alt=false, $align=false, $extras=false, $dir=false, $border=0) {
    global $HTTP_SERVER_VARS;
    if (!$dir) {
        $dir = '/gifs';
    }
    if ($size = @getimagesize($HTTP_SERVER_VARS['DOCUMENT_ROOT'].$dir.'/'.$file)) {
        $image = sprintf('<img src="%s/%s" border="%d" %s alt="%s" %s%s />',
            $dir,
            $file,
            $border,
            $size[3],
            ($alt    ? $alt : ''),
            ($align  ? ' align="'.$align.'"'  : ''),
            ($extras ? ' '.$extras            : '')
        );
    } else {
        $image = sprintf('<img src="%s/%s" border="%d" alt="%s" %s%s />',
            $dir,
            $file,
            $border,
            ($alt    ? $alt : ''),
            ($align  ? ' ALIGN="'.$align.'"'  : ''),
            ($extras ? ' '.$extras            : '')
        );
    }
    return $image;
}



// print_image()
// print an IMG tag for a given file
//

function print_image($file, $alt=false, $align=false, $extras=false, $dir=false, $border=0) {
    print make_image($file, $alt, $align, $extras, $dir);
}

// delim()
// print a pipe delimiter
//

function delim($color=false) {
    if (!$color) {
        return '&nbsp;|&nbsp;';
    }
    return sprintf('<font color="%s">&nbsp;|&nbsp;</font>', $color );
}



// hdelim()
// print a horizontal delimiter (just a wide line);
//

function hdelim() {
    return '<hr />';
}



// make_link()
// return a hyperlink to something, within the site
//

function make_link ($url, $linktext=false, $target=false, $extras=false) {
    return sprintf("<a href=\"%s\"%s%s>%s</a>",
        $url,
        ($target ? ' target="'.$target.'"' : ''),
        ($extras ? ' '.$extras : ''),
        ($linktext ? $linktext : $url)
    );
}

// make_mailto_link()
// return a mailto-hyperlink
//

function make_mailto_link ($url, $linktext=false, $extras=false) {
    return make_link("mailto:" . $url, ($linktext ? $linktext : $url), false, $extras);
}

// print_link()
// echo a hyperlink to something, within the site
//

function print_link($url, $linktext=false, $target=false, $extras=false) {
    echo make_link($url, $linktext, $target, $extras);
}



// commonheader()
//
//

function commonHeader($title) {
    global $SIDEBAR_DATA, $ONLOAD;

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
 <title>PEAR :: <?php echo $title; ?></title>
 <link rel="shortcut icon" href="/gifs/favicon.ico" />
 <link rel="stylesheet" href="/style.css" />
 <link rel="alternate" type="application/rss+xml" title="RSS feed" href="http://<?php echo $_SERVER['HTTP_HOST']; ?>/rss.php" />
</head>

<body <?php
    if (!empty($GLOBALS['ONLOAD'])) {
        print "onload=\"" . $GLOBALS['ONLOAD']. "\"";
    }
?>
        bgcolor="#ffffff"
        text="#000000"
        link="#006600"
        alink="#cccc00"
        vlink="#003300"
><a name="TOP" />
<table border="0" cellspacing="0" cellpadding="0" width="100%">
  <tr bgcolor="#339900">
    <td align="left" rowspan="2" width="120" colspan="2" height="1">
<?php print_link('/', make_image('pearsmall.gif', 'PEAR', false, 'vspace="5" hspace="5"') ); ?><br />
    </td>
    <td align="right" valign="top" colspan="3" height="1">
      <font color="#ffffff"><b>
        <?php echo strftime("%A, %B %d, %Y"); ?>
      </b>&nbsp;<br />
     </font>
    </td>
  </tr>

  <tr bgcolor="#339900">
    <td align="right" valign="bottom" colspan="3" height="1">
      <?php

    if (empty($_COOKIE['PEAR_USER'])) {
        print_link('/login.php', 'Login', false, 'class="menuBlack"');
    } else {
        print '<span class="menuWhite">logged in as ';
        print strtoupper($_COOKIE['PEAR_USER']);
        print '&nbsp;</span><br />';
        print_link('/?logout=1', 'Logout', false, 'class="menuBlack"');
    }
    echo delim();
    print_link('/manual/', 'Docs', false, 'class="menuBlack"');
    echo delim();
    print_link('/packages.php', 'Packages', false, 'class="menuBlack"');
    echo delim();
    print_link('/support.php','Support',false,'class="menuBlack"');
      ?>&nbsp;<br />
      <?php spacer(2,2); ?><br />
    </td>
  </tr>

  <tr bgcolor="#003300"><td colspan="5" height="1"><?php spacer(1,1);?><br /></td></tr>

  <tr bgcolor="#006600">
    <td align="right" valign="top" colspan="5" height="1">
    <form method="post" action="/search.php">
    <font color="#ffffff"><small>Search for</small>
    <input class="small" type="text" name="search_string" value="" size="20" />
    <small>in the</small>
    <select name="search_in" class="small">
	<option value="packages">Packages</option>
    <option value="pear-dev">Developer mailing list</option>
    <option value="pear-general">General mailing list</option>
    <option value="pear-cvs">CVS commits mailing list</option>
    </select>
    <input type="image" src="/gifs/small_submit_white.gif" alt="search" align="bottom" /></font>&nbsp;<br /></form></td></tr>

  <tr bgcolor="#003300"><td colspan="5" height="1"><?php spacer(1,1);?><br /></td></tr>

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




// commonfooter()
//
//

function commonFooter() {
    global $LAST_UPDATED, $MIRRORS, $MYSITE, $COUNTRIES,$SCRIPT_NAME, $RSIDEBAR_DATA;
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

  <tr bgcolor="#003300"><td colspan="5" height="1"><?php spacer(1,1);?><br /></td></tr>
  <tr bgcolor="#339900">
      <td align="right" valign="bottom" colspan="5" height="1">
<?php
print_link('/about/privacy.php', 'PRIVACY POLICY', false, 'class="menuBlack"');
echo delim();
print_link('/credits.php', 'CREDITS', false, 'class="menuBlack"');
?>
      <br />
      </td>
  </tr>
  <tr bgcolor="#003300"><td colspan="5" height="1"><?php spacer(1,1); ?><br /></td></tr>

  <tr valign="top" bgcolor="#cccccc">
    <td colspan="5" height="1">
	  <table border="0" cellspacing="0" cellpadding="5" width="100%">
	  	<tr>
		 <td>
		  <small>
	      <?php print_link('/copyright.php', 'Copyright &copy; 2001, 2002, 2003 The PHP Group'); ?><br />
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
?>
