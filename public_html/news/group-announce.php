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
   | Authors: Martin Jansen <mj@php.net>                                  |
   +----------------------------------------------------------------------+
   $Id: group-announce.php 139605 2003-09-04 15:42:31Z mj $
*/

response_header("ActiveState Active Award for Stig Bakken");
?>

<h1>Announcing the PEAR Group</h1>

<div style="margin-left:2em;margin-right:2em">

<p>On 12th August 2003 <?php echo user_link("ssb", true); ?>, the founder of PEAR, announced
the forming of the PEAR Group, which will be the governing body of
PEAR. The full announcement can be
<?php echo make_link("http://marc.theaimsgroup.com/?l=pear-dev&m=106073080219083&w=2",
                     "found here"); ?>.</p>
<p>More information about the Group, including a first administrative
document, can be found at a <?php echo make_link("/group/", "dedicated place"); ?> 
on pear.php.net.</p>

</div>

<?php response_footer(); ?>