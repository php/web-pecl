<?php

require_once "pear-config.php";
require_once "pear-debug.php";
require_once "pear-database.php";

$dbh = DB::connect(PEAR_DATABASE_DSN, array('persistent' => true));
renumber_visitations(true);

?>