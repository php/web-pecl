<?php
/* $Id$ */


require_once 'site.php';

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
    $str = substr($str,0,-1) . sprintf(' height="%s" width="%s">', $height, $width );
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



// make_submit()
//  - make a submit button image
//
function make_submit($file, $alt=false, $align=false, $extras=false, $dir=false, $border=0) {
    if (!$dir) {
        $dir = '/gifs';
    }
    $return = make_image($file, $alt, $align, $extras, $dir, $border);
    if ($return != "<img />") {
        $return = '<input type="image"'.substr($return,4);
    } else {
        $return = '<input type="submit">';
    }
    return $return;
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

function hdelim($color="#000000") {
    if (!$color) {
        return '<hr noshade size="1" />';
    }
    return sprintf('<hr noshade size="1" color="%s" />', $color );
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
    global $SIDEBAR_DATA, $HTTP_SERVER_VARS;

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
 <title>PEAR :: <?php echo $title; ?></title>
 <link rel="stylesheet" href="/style.css" />
</head>

<body
	topmargin="0" leftmargin="0"
	marginheight="0" marginwidth="0"
        background="/gifs/beta_bg.gif"
        bgcolor="#ffffff"
        text="#000000"
        link="#006600"
        alink="#cccc00"
        vlink="#003300"
><a name="TOP" /></a>
<table border="0" cellspacing="0" cellpadding="0" height="48" width="100%">
  <tr bgcolor="#339900">
    <td align="left" rowspan="2" width="120">
<?php print_link('/', make_image('pearsmall.gif', 'PEAR', false, 'vspace="2" hspace="5"') ); ?><br />
    </td>
    <td align="right" valign="top">
      <font color="#ffffff"><b>
        <?php echo strftime("%A, %B %d, %Y"); ?>
      </b>&nbsp;<br />
<?php
    if (isset($GLOBALS['HTTP_COOKIE_VARS']['pear_dev'])) {
    print "pear_dev cookie set&nbsp;<br />";
    }
?>      </font>
    </td>
  </tr>

  <tr bgcolor="#339900">
    <td align="right" valign="bottom">
      <?php

    if (isset($HTTP_SERVER_VARS['PHP_AUTH_USER'])) {
        print '<span class="menuWhite">logged in as ';
        print strtoupper($HTTP_SERVER_VARS['PHP_AUTH_USER']);
        print '&nbsp;</span><br />';
        print_link('/logout.php?showmsg=1', 'LOGOUT', false, 'class="menuBlack"');
    } else {
        print_link('/login.php', 'LOGIN', false, 'class="menuBlack"');
    }
    echo delim();
    print_link('/manual/', 'DOCS', false, 'class="menuBlack"');
    echo delim();
    print_link('/support.php','SUPPORT',false,'class="menuBlack"');
    echo delim();
    print_link('/faq.php','FAQ',false,'class="menuBlack"');
      ?>&nbsp;<br />
      <?php spacer(2,2); ?><br />
    </td>
  </tr>

  <tr bgcolor="#003300"><td colspan="2"><?php spacer(1,1);?><br /></td></tr>

  <tr bgcolor="#006600">
    <td align="right" valign="top" colspan="2">
    <form method="post" action="/search.php">
    <font color="#ffffff"><small>search for</small>
    <input class="small" type="text" name="search_string" value="" size="20" />
    <small>in the</small>
    <select name="search_in" class="small">
	<option value="packages">Packages</option>
    <option value="pear-dev">Developer mailing list</option>
    <option value="pear-general">General mailing list</option>
    <option value="pear-cvs">CVS commits mailing list</option>
    </select>
    <?php echo make_submit('small_submit_white.gif', 'search', 'bottom'); ?></font>&nbsp;<br /></form></td></tr>

  <tr bgcolor="#003300"><td colspan="2"><?php spacer(1,1);?><br /></td></tr>
</table>

<table cellpadding="0" cellspacing="0" width="100%">
 <tr valign="top">
<?php if (isset($SIDEBAR_DATA)) { ?>
  <td bgcolor="#f0f0f0" width="149">
   <table width="149" cellpadding="4" cellspacing="0">
    <tr valign="top">
     <td style="font-size: 90%"><?php echo $SIDEBAR_DATA?><br /></td>
    </tr>
   </table>
  </td>
  <td bgcolor="#cccccc" width="1" background="/gifs/checkerboard.gif"><?php spacer(1,1);?><br /></td>
<?php } ?>
  <td width="625">
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
  <td bgcolor="#cccccc" width="1" background="/gifs/checkerboard.gif"><?php spacer(1,1);?><br /></td>
  <td width="149" bgcolor="#f0f0f0">
    <table width="100%" cellpadding="4" cellspacing="0"><tr valign="top"><td class="sidebar"><?php echo $RSIDEBAR_DATA; ?><br /></td></tr></table>
  </td>
<?php } ?>
 </tr>
</table>

<table border="0" cellspacing="0" cellpadding="0" width="100%">
  <tr bgcolor="#003300"><td><?php spacer(1,1);?><br /></td></tr>
  <tr bgcolor="#339900">
      <td align="right" valign="bottom">
<?php
print_link('/source.php?url='.$SCRIPT_NAME, 'SHOW SOURCE', false, 'class="menuBlack"');
echo delim();
print_link('/credits.php', 'CREDITS', false, 'class="menuBlack"');

/**
 * For now (2001-12-02) we don't have any mirror
 */
if (0) { ?>

      <script language="javascript" type="text/javascript">
      <!--
        function gotomirror(form) {
          url = form.country.options[form.country.selectedIndex].value;
          if (url != '<? echo $MYSITE; ?>') {
            window.location.href = url;
          }
      return false;
        }
      //-->
      </script>
      <form method="get" action="/mirrors.php" onsubmit="return gotomirror(this);">
      <input type="hidden" name="REDIRECT" value="1">
      <?php
    echo delim();
    print_link('/mirrors.php', 'MIRRORS:', false, 'class="menuBlack"');
    echo "&nbsp;<select class=\"small\" name=\"country\" onchange=\"gotomirror(this.form)\">\n";

    foreach($MIRRORS as $url=>$mirror) {
        if ($mirror[4] == 1) { /* only list full mirrors here */
            if ($url==$MYSITE) {
                echo '<option value="' . $url . '" SELECTED>' . $COUNTRIES[$mirror[0]] .
                     ' (' . $mirror[1] . ") *\n";
            } else {
                echo '<option value="' . $url . '">' . $COUNTRIES[$mirror[0]] .
                     ' (' . $mirror[1] . ")\n";
            }
        }
    }
    echo "</select> ";
    echo make_submit('small_submit_black.gif', 'go', 'bottom' );
    ?>&nbsp;<br />
      </form>
<?php } ?><br />
      </td>
  </tr>
  <tr bgcolor="#003300"><td><?php spacer(1,1); ?><br /></td></tr>
</table>

<table border="0" cellspacing="0" cellpadding="6" width="100%">
  <tr valign="top" bgcolor="#cccccc">
    <td><small>
      <?php print_link('http://www.php.net/', make_image('php-logo.gif', 'PHP', 'left') ); ?>
      <?php print_link('/copyright.php', 'Copyright &copy; 2001, 2002 The PHP Group'); ?><br />
      All rights reserved.<br />
      </small>
    </td>
    <td align="right"><small>
      Webspace generously provided by:
      <?php print_link($MIRRORS[$MYSITE][3], $MIRRORS[$MYSITE][1] ); ?><br />
      Last updated: <?php echo $LAST_UPDATED; ?><br />
      </small>
    </td>
  </tr>
</table>

</body>
</html>
<?php
}
?>
