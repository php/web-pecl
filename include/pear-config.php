<?php

if (isset($_SERVER['PEAR_TMPDIR'])) {
    define('PEAR_TMPDIR', $_SERVER['PEAR_TMPDIR']);
    define('PEAR_CVS_TMPDIR', $_SERVER['PEAR_TMPDIR'].'/cvs');
    define('PEAR_UPLOAD_TMPDIR', $_SERVER['PEAR_TMPDIR'].'/uploads');
} else {
    define('PEAR_TMPDIR', '/var/tmp/pear');
    define('PEAR_CVS_TMPDIR', '/var/tmp/pear/cvs');
    define('PEAR_UPLOAD_TMPDIR', '/var/tmp/pear/uploads');
}

if (isset($_SERVER['PEAR_DATABASE_DSN'])) {
    define('PEAR_DATABASE_DSN', $_SERVER['PEAR_DATABASE_DSN']);
} else {
    define('PEAR_DATABASE_DSN', 'mysql://pear:pear@localhost/pear');
}
if (isset($_SERVER['PEAR_AUTH_REALM'])) {
    define('PEAR_AUTH_REALM', $_SERVER['PEAR_AUTH_REALM']);
} else {
    define('PEAR_AUTH_REALM', 'PEAR');
}
if (isset($_SERVER['PEAR_TARBALL_DIR'])) {
    define('PEAR_TARBALL_DIR', $_SERVER['PEAR_TARBALL_DIR']);
} else {
    define('PEAR_TARBALL_DIR', '/var/lib/pear');
}
if (isset($_SERVER['PHP_CVS_REPO_DIR'])) {
    define('PHP_CVS_REPO_DIR', $_SERVER['PHP_CVS_REPO_DIR']);
} else {
    define('PHP_CVS_REPO_DIR', '/repository/pear');
}

?>
