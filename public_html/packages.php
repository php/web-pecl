<?php

response_header("PEAR :: Packages");

if (empty($domain)) {
    $package_where = "packages.parent IS NULL";
    $package_title = "Package Browser";
} else {
    $package_where = "packages.parent = '$domain'";
    $package_title = "Package Browser: $domain";
}

$sth = $dbh->query("SELECT packages.name, packages.leftvisit, packages.rightvisit, packages.placeholder, releases.version FROM packages LEFT JOIN releases ON packages.name = releases.package WHERE $package_where");

//$sth = $dbh->query("SELECT * FROM packages WHERE $package_where");

print "<H2>$package_title</H2>\n";

print "<TABLE CELLSPACING=0 BORDER=0 CELLPADDING=1>";
print "<TR><TD BGCOLOR=\"#000000\">\n";
print "<TABLE CELLSPACING=1 BORDER=0 CELLPADDING=3>\n";
print " <TR BGCOLOR=\"#e0e0e0\">\n";
print "  <TH>Package</TH>\n";
print "  <TH>Stable</TH>\n";
print "  <TH>Sub-packages</TH>\n";
print " </TR>\n";
$i = 0;
while ($sth->fetchInto($row, DB_FETCHMODE_ASSOC) === DB_OK) {
    if (++$i % 2) {
        $bg1 = "#ffffff";
        $bg2 = "#f0f0f0";
    } else {
        $bg1 = "#f0f0f0";
        $bg2 = "#e0e0e0";
    }
    print " <TR>\n";
    extract($row);
    if ($leftvisit && $rightvisit) {
        $lv = $leftvisit + 1;
        $num = $dbh->getOne("SELECT COUNT(name) FROM packages WHERE leftvisit".
                            " BETWEEN $lv AND $rightvisit");
    } else {
        $num = 0;
    }
    print "  <TD BGCOLOR=\"$bg1\">";
    if ($placeholder) {
        print "$name";
    } else {
        print "<A HREF=\"pkginfo.php?package=$name\">$name</A>";
    }
    print "</TD>\n  <TD BGCOLOR=\"$bg2\">";
    if ($version) {
        print "<A HREF=\"pkginfo.php?package=$name&release=$version\">";
        print "$version</A>";
    } else {
        print "&nbsp;";
    }
    print "</TD>\n  <TD BGCOLOR=\"$bg1\">";
    if ($num > 0) {
        print "$num <A HREF=\"$PHP_SELF?domain=$name\">sub-packages</A>";
    } else {
        print "&nbsp;";
    }
    print "</TD>\n </TR>\n";
}
print "</TABLE></TD></TR></TABLE>\n";

response_footer();

?>
