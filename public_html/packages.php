<?php

response_header("PEAR: Packages");

if (empty($domain)) {
    $domain_where = "domains.parent IS NULL";
    $package_where = "(domain IS NULL OR DOMAIN = '')";
    $domain_title = "Domains";
    $package_title = "Packages";
} else {
    $domain_where = "domains.parent = '$domain'";
    $package_where = "domain = '$domain'";
    $domain_title = "Domains in $domain";
    $package_title = "Packages in $domain";
}

$domlist = $dbh->getAssoc("SELECT domains.name AS domain, ".
                          "domains.description AS description, ".
                          "COUNT(packages.name) AS packages ".
                          "FROM domains LEFT JOIN packages ".
                          "ON packages.domain = domains.name ".
                          "WHERE $domain_where ".
                          "GROUP BY domain");
if (sizeof($domlist)) {
    print "<H2>$domain_title</H2>\n";
    while (list($name, $data) = each($domlist)) {
        list($desc, $number) = $data;
        print "<A HREF=\"$PHP_SELF?domain=$name\">$name</A>";
        if ($desc) {
            print " -- $desc";
        }
        if ($number > 0) {
            print " ($number packages)";
        }
        print "<BR>\n";
    }
}

$pkglist = $dbh->getAssoc("SELECT packages.name, packages.summary, ".
                          "packages.stablerelease, packages.develrelease, ".
                          "packages.domain, maintains.handle AS lead ".
                          "FROM packages LEFT JOIN maintains ".
                          "ON packages.name = maintains.package ".
                          "AND maintains.role = 'lead' ".
                          "WHERE $package_where");
if (sizeof($pkglist)) {
    print "<H2>$package_title</H2>\n";
    while (list($name, $more) = each($pkglist)) {
        list($summary, $stable, $devel, $domain, $lead) = $more;
        print "<A HREF=\"pkginfo.php?package=$name\">$name</A>";
        if ($summary) {
            print " -- $summary";
        }
        if ($lead) {
            print " lead:$lead";
        }
        print "<BR>\n";
    }
}

response_footer();

?>
