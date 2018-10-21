<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2003 The PEAR Group                                    |
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
*/
response_header("Damblan");
?>

<h1>Damblan</h1>

<p>Damblan is a set of utility classes, which we are using on
pear.php.net. Some people might even call Damblan a framework, but we
don&apos;t. For now.</p>

<p>The name is derived from the Nepalese summit
<?php echo make_link("http://www.summitpost.com/show/mountain_link.pl/mountain_id/52", "Ama Dablam"); ?>,
but with an additional &quot;m&quot;. Please do not ask for the reason.</p>

<p>Currently the feature set of Damblan is quite limited, but we are
working towards integrating caching, parsing and sending emails and
other great stuff. At a certain point there may be even the
possibility to download and install Damblan as a PEAR package.</p>

<p>If you have questions or comments about Damblan, get in touch with
<?php echo make_mailto_link("mj@php.net", "Martin"); ?>.</p>

<p><?php echo make_link("/about/", "Back"); ?></p>

<?php
response_footer();
?>
