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
*/

require_once "PEAR/Common.php";

auth_require(true);

response_header('Fix Dependencies');

$pc = new PEAR_Common;

$pkg_id2name = $dbh->getAssoc("SELECT id,name FROM packages");
$rel_id2name = $dbh->getAssoc("SELECT r.id,concat_ws('-', p.name, r.version) FROM packages p, releases r WHERE r.package = p.id");

print "<h2>Deleting Existing Dependencies...</h2>\n";
$dbh->setOption("optimize", "portability");
$dbh->query("DELETE FROM deps");
$ar = $dbh->affectedRows();
$dbh->setOption("optimize", "performance");
print "$ar rows deleted<br />\n";

print "<h2>Inserting New Dependencies...</h2>\n";
$sth = $dbh->query("SELECT package, release, fullpath FROM files");
while ($sth->fetchInto($row)) {
	list($package, $release, $fullpath) = $row;
	printf("<h3>%s (package %d, release %d):</h3>\n",
		   basename($fullpath), $package, $release);
	if (!@file_exists($fullpath)) {
		continue;
	}
	$pkginfo = $pc->infoFromTgzFile($fullpath);
	if (empty($pkginfo['release_deps'])) {
		printf("%s : no dependencies<br />\n", $rel_id2name[$release]);
		continue;
	}
	foreach ($pkginfo['release_deps'] as $dep) {
		if ($dep['rel']) {
			$dep['relation'] = $dep['rel'];
			unset($dep['rel']);
		}
		$i = 0;
		$fields = implode(',', array_keys($dep));
		$values = array_values($dep);
		$phs = substr(str_repeat('?,', sizeof($values) + 2), 0, -1);
		$query = "INSERT INTO deps (package,release,$fields) VALUES($phs)";
		$pq = $dbh->prepare($query);
		$values = array_merge(array($package, $release), $values);
		if ($dep['type'] == 'php') {
			printf("%s : php %s %s %s<br />\n", $rel_id2name[$release],
				   $dep['relation'], $dep['version']);
		} elseif ($dep['relation'] == 'has') {
			printf("%s : (%s) %s %s<br />\n", $rel_id2name[$release],
				   $dep['type'], $dep['name']);
		} else {
			printf("%s : (%s) %s %s %s<br />\n", $rel_id2name[$release],
				   $dep['type'], $dep['name'], $dep['relation'],
				   @$dep['version']);
		}
		$dbh->execute($pq, $values);
	}
}

response_footer();

?>
