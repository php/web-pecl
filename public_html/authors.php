<?php

response_header("Users");

$page_size = 20;

print "<H1>Users</H1>\n";

$all_firstletters = $dbh->getCol('SELECT SUBSTRING(handle,1,1) FROM users '.
								 'WHERE registered = 1 ORDER BY handle');
// I wish there was a way to do this in mysql...
$first_letter_offsets = array();
for ($i = 0; $i < sizeof($all_firstletters); $i++) {
	$l = $all_firstletters[$i];
	if (isset($first_letter_offsets[$l])) {
		continue;
	}
	$first_letter_offsets[$l] = $i;
}

if (preg_match('/^[a-z]$/i', @$letter)) {
	$offset = $first_letter_offsets[$letter];
	$offset -= $offset % $page_size;
}

if (empty($show)) {
	$show = $page_size;
} else {
	settype($show, "int");
}
settype($offset, "int");

$nauthors = $dbh->getOne("SELECT COUNT(handle) FROM users ".
						 "WHERE registered = 1");

$last_shown = $offset + $page_size - 1;

$firstletters = array_unique($all_firstletters);

$last = $offset - $page_size;
$lastlink = "$PHP_SELF?offset=$last";
$next = $offset + $page_size;
$nextlink = "$PHP_SELF?offset=$next";
print "<table border=\"0\" cellspacing=\"1\" cellpadding=\"5\">\n";
print " <tr bgcolor=\"#cccccc\">\n";
print "  <th>";
if ($offset > 0) {
	print "<a href=\"$lastlink\">&lt;&lt; Last $page_size</a>";
} else {
	print "&nbsp;";
}
print "</th>\n";
print "  <td colspan=\"3\">";

foreach ($firstletters as $fl) {
	$o = $first_letter_offsets[$fl];
	if ($o >= $offset && $o <= $last_shown) {
		printf('<b>%s</b> ', strtoupper($fl));
	} else {
		printf('<a href="%s?letter=%s">%s</a> ',
			   $PHP_SELF, $fl, strtoupper($fl));
	}		   
}
printf("<br />Displaying users %d - %d of %d<br />\n",
	   $offset, min($offset+$show, $nauthors), $nauthors);
$sth = $dbh->limitQuery('SELECT handle,name,email,homepage,showemail '.
						'FROM users WHERE registered = 1 ORDER BY handle',
						$offset, $show);
if (DB::isError($sth)) {
    die("query failed: ".DB::errorMessage($dbh)."<BR>\n");
}
print "</td>\n";
print "  <th>";
if ($offset + $page_size < $nauthors) {
	$nn = min($page_size, $nauthors - $offset - $page_size);
	print "<a href=\"$nextlink\">Next $nn &gt;&gt;</a>";
} else {
	print "&nbsp;";
}
print "</th>\n";
print " </tr>\n";

print " <tr bgcolor=\"#CCCCCC\">\n";
print "  <th>Handle</th>\n";
print "  <th>Name</th>\n";
print "  <th>Email</th>\n";
print "  <th>Homepage</th>\n";
print "  <th>Commands</th>\n";
print " </tr>\n";

$rowno = 0;
while (is_array($row = $sth->fetchRow(DB_FETCHMODE_ASSOC))) {
    extract($row);
    if (++$rowno % 2) {
        print " <tr bgcolor=\"#e8e8e8\">\n";
    } else {
        print " <tr bgcolor=\"#e0e0e0\">\n";
    }
    print "  <td>$handle</td>\n";
    print "  <td>$name</td>\n";

    if ($showemail) {
        print "  <td><a href=\"mailto:$email\">$email</a></td>\n";
    } else {
        print "  <td>(not shown)</td>\n";
    }
    if (!empty($homepage)) {
        print "<td><a href=\"$homepage\">$homepage</a></td>";
    } else {
        print '<td>&nbsp;</td>';
    }
    print "\n  <td><a href=\"edit-author.php?handle=".$row['handle']."\">[E]</a>&nbsp;
                 <a href=\"detail-author.php?handle=".$row['handle']."\">[I]</A></td>\n";
    print " </tr>\n";
}

print "</table>\n";

response_footer();

?>
