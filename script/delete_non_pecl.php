<?php
/*
 * Drop all non pecl (aka pear) packages
 * remove the bugs entry
 * remove the releases entries and files
 * Author: pierre@php.net
 */
/* $Id$ */
putenv('PEAR_DATABASE_DSN', ' mysqli://root:n0ki41945@127.0.0.1/peclweb');
putenv('PEAR_TMPDIR', '/home/pierre/projects/pecl/tmp');
putenv('PEAR_TARBALL_DIR', '/home/pierre/projects/pecl/pecl/public_html/packages'); 

include __DIR__ . '/../include/pear-config.php';
// {{{ rm_rf()
function rm_rf($path)
{
	echo "delete <$path>\n";
	return;
    // Some sanity checks
    if (empty($path)) {
        return false;
    }
    if (@is_dir($path) && is_writable($path)) {
        $dp = opendir($path);
        while ($ent = readdir($dp)) {
            if ($ent == '.' || $ent == '..') {
                continue;
            }
            $file = $path . DIRECTORY_SEPARATOR . $ent;
            if (@is_dir($file)) {
                rm_rf($file);
            } elseif (is_writable($file)) {
                unlink($file);
            } else {
                echo $file . "is not writable and cannot be removed.
Please fix the permission or select a new path.\n";
            }
        }
        closedir($dp);
        return rmdir($path);
    } else {
        return @unlink($path);
    }
}
// }}}

if (0) {
if (defined('PEAR_TARBALL_DIR')) {
    $pkg_dir = PEAR_TARBALL_DIR;
} else {
    $pkg_dir = '/home/pierre/project/pecl/pecl-20091213/packages';
}
if (defined('PEAR_REST_DIR')) {
    $rest_dir = PEAR_REST_DIR;
} else {
    $rest_dir = __DIR__ . '../public_html/rest';
}
if (!is_dir($pkg_dir)) {
    echo "Invalid packages direcory $pkg_dir\n";
    exit(1);
}
if (!is_dir($rest_dir)) {
    echo "Invalid rest direcory $rest_dir\n";
    exit(1);
}
if (is_dir($rest_dir) && !is_dir($rest_dir . '/p')) {
    echo "Invalid rest direcory $rest_dir\n";
    exit(1);
}
}
$dh = new PDO(PECL_DB_DSN, PECL_DB_USER, PECL_DB_PASSWORD);

// Get the filenames and remove them, then delete the records
$sql = '
SELECT basename FROM files WHERE package IN
(SELECT id FROM packages WHERE package_type != "pecl")
ORDER BY basename
';

$res = $dh->query($sql);
foreach ($res as $row) {
    if (file_exists($pkg_dir . '/' . $row['basename'])) {
//        unlink($pkg_dir . '/' . $row['basename']);
        echo $pkg_dir . '/' . $row['basename'] . " deleted\n";
    }
}

$sql = 'SELECT LOWER(name) as name FROM packages WHERE package_type != "pecl"';
$res = $dh->query($sql);
foreach ($res as $pkg) {
    $r = $rest_dir . '/r/' . $pkg['name'];
    $p = $rest_dir . '/p/' . $pkg['name'];
    if (is_dir($r)) {
        rm_rf($r);
        echo $r . " removed\n";
    }
    if (is_dir($p)) {
        rm_rf($p);
        echo $p . " removed\n";
    }

}

$sql = '
DELETE FROM files WHERE package IN
(SELECT id FROM packages WHERE package_type != "pecl")';
$res = $dh->query($sql);


$sql = '
DELETE FROM package_stats WHERE package IN 
(SELECT name FROM packages WHERE package_type != "pecl");';
$res = $dh->query($sql);


// Cleanup the bugs DB
$sql = '
DELETE FROM bugdb_comments WHERE bug IN
	(
		SELECT id from bugdb WHERE package_name IN (
			SELECT name FROM packages WHERE package_type!="pecl"
		)
	)
';
$res = $dh->query($sql);

$sql = '
DELETE FROM bugdb_obsoletes_patches WHERE bugdb_id IN
	(
		SELECT id from bugdb WHERE package_name IN (
			SELECT name FROM packages WHERE package_type!="pecl"
		)
	)
';
$res = $dh->query($sql);

$sql = '
DELETE FROM bugdb_patchtracker WHERE bugdb_id IN
	(
		SELECT id from bugdb WHERE package_name IN (
			SELECT name FROM packages WHERE package_type!="pecl"
		)
	)
';
$res = $dh->query($sql);

$sql = '
DELETE FROM bugdb_subscribe WHERE bug_id IN
	(
		SELECT id FROM bugdb WHERE package_name IN (
			SELECT name FROM packages WHERE package_type!="pecl"
		)
	)
';
$res = $dh->query($sql);

$sql = '
DELETE FROM bugdb_votes WHERE bug IN
	(
		SELECT id FROM bugdb WHERE package_name IN (
			SELECT name FROM packages WHERE package_type!="pecl"
		)
	)
';

$sql = '
DELETE FROM bugdb_roadmap WHERE package IN (
		SELECT name FROM packages  WHERE package_type!="pecl"
)
';
$res = $dh->query($sql);

