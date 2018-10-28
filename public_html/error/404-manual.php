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
  | Authors:                                                             |
  +----------------------------------------------------------------------+
*/

response_header("Error 404");

?>

<h2>Error 404 - document not found</h2>

<p>The requested document <i><?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?></i> was not
found in the PEAR manual.</p>

<p>Please go to the <?php print_link("/manual/", "Table of Contents"); ?>
and try to find the desired chapter there.</p>

<?php

response_footer();
