<?php

response_header("PEAR: Packages");

if (empty($domain)) {
    $package_where = "parent IS NULL";
    $package_title = "Packages";
} else {
    $package_where = "parent = '$domain'";
    $package_title = "Packages in $domain";
}

$sth = $dbh->query("SELECT packages.name,packages.leftvisit,packages.rightvisit,releases.release FROM packages LEFT JOIN releases ON packages.name = releases.package WHERE packages.parent IS NULL");

//$sth = $dbh->query("SELECT * FROM packages WHERE $package_where");

print "<H2>$package_title</H2>\n";

while ($sth->fetchInto($row, DB_FETCHMODE_ASSOC) === DB_OK) {
    extract($row);
    if ($leftvisit && $rightvisit) {
        $lv = $leftvisit + 1;
        $num = $dbh->getOne("SELECT COUNT(name) FROM packages WHERE leftvisit".
                            " BETWEEN $lv AND $rightvisit");
    } else {
        $num = 0;
    }
    print "<A HREF=\"pkginfo.php?package=$name\">$name</A>";
    if ($release) {
        print " <A HREF=\"pkginfo.php?package=$name&release=$release\">";
        print "$release</A>";
    }
    if ($num > 0) {
        print " ($num sub-packages)";
    }
    print "<BR>\n";
}

response_footer();

?>
