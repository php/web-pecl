<?php

/*
  +----------------------------------------------------------------------+
  | The PECL website                                                     |
  +----------------------------------------------------------------------+
  | Copyright (c) 1999-2018 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | https://php.net/license/3_01.txt                                     |
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
<?php echo make_link("https://www.summitpost.org/ama-dablam/150234", "Ama Dablam"); ?>,
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
