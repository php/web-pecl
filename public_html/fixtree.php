<?php

Header("Content-type: text/plain");
$dbh->setErrorHandling(PEAR_ERROR_DIE);

print "$REQUEST_URI\n\n";

renumber_visitations(true);

?>
