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

if (isset($country)) {
  header("Location: http://$country.pear.php.net/");
}
response_header("Mirrors Page");
?>

<h2>Mirror Sites</h2>

<p>
Here you can find more information about the mirrors
of pear.php.net. Pick a mirror site close to you, or visit
the provider's homepage:
</p>

<p>
<table border="0" cellpadding="2" cellspacing="1" width="100%">
 <tr bgcolor="#cccccc">
  <th>Country</th>
  <th>Mirror Address</th>
  <th>Provider</th>
  <th>Default Language</th>
 </tr>
<?php
  $mprevious = 'aa';
  foreach ($MIRRORS as $murl => $mdata) {
    if ($mdata[4] != 1) { continue; }
    echo '<tr bgcolor="#e0e0e0"><td>';
    if ($mprevious != $mdata[0]) { echo $COUNTRIES[$mdata[0]]; }
    else { echo "&nbsp;"; }
    echo '</td><td>' . make_link($murl, $murl) .
         '</td><td>' . make_link($mdata[3], $mdata[1]) . 
         '</td><td>' . $LANGUAGES[$mdata[6]] . '</td></tr>';
    $mprevious = $mdata[0];
  }
?>
</table>
</p>

<!--
<p>
If you are interested in hosting a mirror of this site, 
<?php print_link("/README.mirror", "here's how"); ?>.
</p>
//-->

<?php
response_footer();
?>
