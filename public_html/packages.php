<?php

response_header("PEAR :: Packages");

if (empty($domain)) {
    $package_where = "packages.parent IS NULL";
    $package_title = "Package Browser";
} else {
    $package_where = "packages.parent = '$domain'";
    $package_title = "Package Browser: $domain";
}

$sth = $dbh->query("SELECT packages.name, packages.leftvisit, packages.rightvisit, packages.virtual, releases.version FROM packages LEFT JOIN releases ON packages.name = releases.package WHERE $package_where");

//$sth = $dbh->query("SELECT * FROM packages WHERE $package_where");

print "<h2>$package_title</h2>\n";

border_box_start(array("Package", "Stable", "Sub-packages"));
$i = 0;
while ($sth->fetchInto($row, DB_FETCHMODE_ASSOC) === DB_OK) {
    if (++$i % 2) {
        $bg1 = "#ffffff";
        $bg2 = "#f0f0f0";
    } else {
        $bg1 = "#f0f0f0";
        $bg2 = "#e0e0e0";
    }
    print " <tr>\n";
    extract($row);
    if ($leftvisit && $rightvisit) {
        $lv = $leftvisit + 1;
        $num = ($rightvisit - $leftvisit - 1) / 2;
        //$num = $dbh->getOne("SELECT COUNT(name) FROM packages WHERE leftvisit BETWEEN $lv AND $rightvisit");
    } else {
        $num = 0;
    }
    print "  <td bgcolor=\"$bg1\">";
    if ($placeholder) {
        print "$name";
    } else {
        print "<a href=\"pkginfo.php?package=$name\">$name</a>";
    }
    print "</td>\n  <td bgcolor=\"$bg2\">";
    if ($version) {
        print "<a href=\"pkginfo.php?package=$name&release=$version\">";
        print "$version</a>";
    } else {
        print "&nbsp;";
    }
    print "</td>\n  <td bgcolor=\"$bg1\">";
    if ($num > 0) {
        print "$num <a href=\"$PHP_SELF?domain=$name\">sub-packages</a>";
    } else {
        print "&nbsp;";
    }
    print "</td>\n </tr>\n";
}
border_box_end();

response_footer();

?>
