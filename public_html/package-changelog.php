<?php
/*
Expected GET vars: pacid
*/
if ($pacid != (string)(int)$pacid) {
    die('Invalid package id');
}
$name = package::info($pacid, 'name');

response_header("$name Changelog");
print '<p>' . make_link("package-info.php?pacid=$pacid", 'Return') . '</p>';
$bb = new Borderbox("$name Changelog");

$sql = "SELECT releases.version AS version, ".
       "DATE_FORMAT(releases.releasedate, '%Y-%m-%d') AS releasedate, ".
       "releases.releasenotes AS releasenotes, ".
       "releases.state AS state ".
       "FROM releases ".
       "WHERE releases.package = $pacid ".
       "ORDER BY releases.releasedate DESC";

$res = $dbh->query($sql);

if ($res->numRows() < 1) {
    print "<center><p><i>No releases yet</i></p></center>";
} else {
    print "<table width=\"100%\" border=\"0\">\n";

    while ($res->fetchInto($row, DB_FETCHMODE_ASSOC)) {
        extract($row);
        print "<tr><td><p><b>Version: $version-$state ($releasedate)".
              "</b></p></td></tr>\n".
              '<tr><td>' . nl2br($releasenotes) ."<br /></td></tr>\n";
    }
    print "</table>\n";
}
$bb->end();
print '<p>' . make_link("package-info.php?pacid=$pacid", 'Return') . '</p>';
response_footer();
?>