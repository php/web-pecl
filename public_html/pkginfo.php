<?php
//error_reporting(E_ALL);

// expected url vars: pacid
$pacid = (isset($pacid)) ? (int) $pacid : null;
if (empty($pacid)) {
    die ('No package selected');
}
// ** expected

define('PHP_CVS_REPO_DIR', '/repository/pear');

if (DB::isError($dbh)) {
    die("DB::Factory failed: ".DB::errorMessage($dbh)."<BR>\n");
}

$dbh->setFetchmode(DB_FETCHMODE_ASSOC);

// Package data
$row = $dbh->getRow("SELECT * FROM packages WHERE id = $pacid");
if (!$row) {
    die ('No package selected (db)');
}
$name        = $row['name'];
$summary     = stripslashes($row['summary']);
$license     = $row['license'];
$description = stripslashes($row['description']);
$category    = $row['category'];

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

$releases = $dbh->getAll(
	"SELECT id, version, state, releasedate, releasenotes
     FROM releases
     WHERE package = $pacid
     ORDER BY releasedate DESC");

$sth = $dbh->query("SELECT f.id AS id, f.release AS release,
                           f.platform AS platform, f.format AS format,
                           f.md5sum AS md5sum, f.basename AS basename,
                           f.fullpath AS fullpath, r.version AS version
                      FROM files f, releases r
                     WHERE f.package = $pacid AND f.release = r.id");
while ($sth->fetchInto($row)) {
	$downloads[$row['version']][] = $row;
}

response_header("Package :: $name");

?>

<!-- PKGINFO start -->
<?php html_category_urhere($category, true); ?>

<h2 align="center"><?php echo "$name";?></h2>

<?php $bb = new BorderBox("Package Information"); ?>

<table border="0" cellspacing="2" cellpadding="2" height="48" width="100%">
<tr>
    <th class="pack" bgcolor="#009933" width="20%">Summary</th>
    <td><?php echo $summary;?></td>
</tr>
<tr>
    <th class="pack" bgcolor="#009933" width="20%">Maintainers</th>
    <td>
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
    <th class="pack" bgcolor="#009933" width="20%">Description</th>
    <td><?php echo $description;?>&nbsp;</td>
</tr>
<tr>
    <td colspan="2" align="right">
<?php print_link("/package-edit.php?id=" . @$_GET['pacid'],
        make_image("edit.gif", "Edit package information")); ?>
&nbsp;
<?php print_link("/package-delete.php?id=" . @$_GET['pacid'],
        make_image("delete.gif", "Delete package")); ?>
    </td>
</tr>
</table>

<?php $bb->end(); ?>

<br>
<table border="0" cellspacing="3" cellpadding="3" height="48" width="100%" align="center">
<tr>
<?php
    // CVS link
    if (@is_dir(PHP_CVS_REPO_DIR . "/$name")) {
        $cvs_link = "[ " . make_link("http://cvs.php.net/cvs.php/pear/$name",
                                     'View Source Code &amp; Docs', 'top')
                         . " ] ";
    } else {
        $cvs_link = '&nbsp;';
    }
    
    // Download link
    $get_link = make_link("/get/$name", 'Download Lastest');
?>
    <td width="33%" align="center">[ <?php echo $get_link; ?> ]</td>
    <td width="33%" align="center"><?php echo $cvs_link;?></td>
    <!-- <td width="33%" align="center">| View ChangeLog |</td> -->
</tr>
</table>

<br>

<?php
$bb = new BorderBox("Available Releases");

if (count($releases) == 0) {
    echo "This package has not released any versions yet.";
} else {
?>
    <table border="0" cellspacing="0" cellpadding="3" width="100%">
        <th align="left">Version</th>
        <th align="left">State</th>
        <th align="left">Release Date</th>
        <th align="left">Downloads</th>

    <?php

    foreach ($releases as $rel) {
	    print " <tr>\n";
	    if (empty($rel['state'])) {
		    $rel['state'] = 'devel';
	    }
	    $rel['releasedate'] = substr($rel['releasedate'], 0, 10);
	    $downloads_html = '';
	    foreach ($downloads[$rel['version']] as $dl) {
    		$downloads_html .= "<a href=\"/get/$dl[basename]\">".
	    		 "$dl[basename]</a><br />";
	    }
	    printf("  <td>%s</td><td>%s</td><td>%s</td><td>%s</td>\n",
                $rel['version'], $rel['state'], $rel['releasedate'],
                $downloads_html);
        print " </tr>\n";
    }
}
?></table>
<?php
$bb->end();

echo "<br /><br />\n";

$bb = new Borderbox("Package dependencies");

$query = "SELECT * FROM deps WHERE package = '" . $_GET['pacid'] . "'";

$sth = $dbh->query($query);

if ($sth->numRows() == 0) {
    echo "<i>This package has no dependencies towards other packages.</i>\n";
} else {

    while ($row = $sth->fetchRow(DB_FETCHMODE_ASSOC)) {
        echo "Dependencies for version " . $row['version'] . ":<br />\n";
        echo "<blockquote>" . $row['deps'] . "</blockquote><br />\n";        
    }    
}
$bb->end();
?>

<!-- PKGINFO end -->

<?php
response_footer();
?>
