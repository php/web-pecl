<?php
//error_reporting(E_ALL);

// expected url vars: pacid
$pacid = (isset($pacid)) ? (int) $pacid : null;
if (empty($pacid)) {
    die ('No package selected');
}
// ** expected

if (DB::isError($dbh)) {
    die("DB::Factory failed: ".DB::errorMessage($dbh)."<BR>\n");
}

$dbh->setFetchmode(DB_FETCHMODE_ASSOC);

// Package data
$row = $dbh->getRow("SELECT * FROM packages WHERE id = $pacid");
if (!$row) {
    die ('No package selected (db)');
}
$name     = $row['name'];
$summary  = $row['summary'];
$license  = $row['license'];

// Releases Data
$maturity = 'stable';
$release  = '1.3';
$release_date  = '2000-01-34';
$release_notes = 'Bug fix release';

// Accounts data
$sth = $dbh->query("SELECT u.handle, u.name, u.email, u.showemail, m.role
                   FROM maintains m, users u
                   WHERE m.package = $pacid
                   AND m.handle = u.handle");
$accounts  = '';
while ($sth->fetchInto($row)) {
    $accounts .= "<tr><td>{$row['name']}";
    if ($row['showemail'] == 1) {
        $accounts .= " &lt;<a href=\"mailto:{$row['email']}\">{$row['email']}</a>&gt;";
    }
    $accounts .= " ({$row['role']}) [<a href=\"account-info.php?handle={$row['handle']}\">details</a>]";
    $accounts .= "</td></tr>\n";
}

response_header("Package :: $name");
?>

<!-- PKGINFO start -->

<center><h2><?php echo "$name $release ($maturity)";?></h2></center>
<table border="1" cellspacing="3" cellpadding="3" height="48" width="100%">
<tr>
    <th class="pack" bgcolor="#009933" width="20%">Summary</th>
    <td><?php echo $summary;?></td>
</tr>
<tr>
    <th class="pack" bgcolor="#009933" width="20%">Accounts</th>
    <td>
        <!-- Accounts -->
        <table border="0" cellspacing="1" cellpadding="1" width="100%">
        <?php echo $accounts;?>
        </table>
    </td>
</tr>
<tr>
    <th class="pack" bgcolor="#009933" width="20%">License</th>
    <td><?php echo $license;?></td>
</tr>
<tr>
    <th class="pack" bgcolor="#009933" width="20%">Release Date</th>
    <td><?php echo $release_date;?></td>
</tr>
<tr>
    <th class="pack" bgcolor="#009933" width="20%">Release Notes</th>
    <td><?php echo $release_notes;?></td>
</tr>
</table>
<br>
<table border="0" cellspacing="3" cellpadding="3" height="48" width="100%" align="center">
<tr>
    <td width="33%" align="center">| View Source Code &amp;<br> Doc On-line |</td>
    <td width="33%" align="center">| View ChangeLog |</td>
    <td width="33%" align="center">| Download Now |</td>
</tr>
</table>
<br>
<!-- Package Dependencies -->
<table border="0" cellspacing="3" cellpadding="3" width="100%">
<tr>
    <th class="others" colspan="3" bgcolor="#DDDDDD">Net_Ping dependencies:</th>
</tr>
<tr>
    <td colspan="3">PHP > 4.0.5</td>
</tr>
<tr>
    <td>_Net_Foo-1.5_</td>
    <td>| Download |</td>
    <td>| View Source Code / Doc On-line |</td>
</tr>
<tr>
    <td colspan="3" align="center">&nbsp;<br>| Download All Packages |</td>
</tr>
</table>
<br>
<!-- Related Links -->
<table border="0" cellspacing="3" cellpadding="3" width="100%">
<tr>
    <th class="others" colspan="3" bgcolor="#DDDDDD">Related Links:</th>
</tr>
<tr>
    <td>_Home Page_</td>
    <td>Description</td>
</tr>
</table>
<br>
<!-- Other releases download -->
<table border="0" cellspacing="3" cellpadding="3" width="100%">
<tr>
    <th class="others" colspan="3" bgcolor="#DDDDDD">Other releases download:</th>
</tr>
<tr>
    <td>_Last CVS version_</td>
</tr>
</table>
<br>

<!-- PKGINFO end -->

<?php
response_footer();
?>
