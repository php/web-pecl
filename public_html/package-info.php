<?php

// {{{ setup, queries

$version = '';
// expected url vars: pacid relid package release
if (isset($_GET['package']) && empty($_GET['pacid'])) {
    $pacid = $dbh->getOne("SELECT id FROM packages WHERE name = ?",
                          array($_GET['package']));
} else {
    $pacid = (isset($_GET['pacid'])) ? (int) $_GET['pacid'] : null;
}

if (isset($_GET['version']) && empty($_GET['relid'])) {
    $relid = $dbh->getOne("SELECT id FROM releases WHERE package = ?".
                          " AND version = ?",
                          array($pacid, $_GET['version']));
} else {
    $relid = (isset($_GET['relid'])) ? (int) $_GET['relid'] : null;
}

if (empty($pacid) && empty($relid)) {
    response_header("Error");
    PEAR::raiseError('No package or release selected');
    response_footer();
    exit();
}
// ** expected

if (DB::isError($dbh)) {
    response_header("Error");
    PEAR::raiseError("DB::Factory failed: ".DB::errorMessage($dbh));
    response_footer();
    exit();
}

$dbh->setFetchmode(DB_FETCHMODE_ASSOC);

// Package data
$pkg = $dbh->getRow("SELECT * FROM packages WHERE id = $pacid");
if (PEAR::isError($pkg) || empty($pkg)) {
    response_header('Invalid package');
    PEAR::raiseError('Invalid package');
    response_footer();
    exit;
}

// Release data
if ($relid) {
    $rel = $dbh->getRow("SELECT * FROM releases WHERE id = $relid");
    if (PEAR::isError($rel) || empty($rel)) {
        response_header('Invalid release');
        PEAR::raiseError('Invalid release');
        response_footer();
        exit;
    }
    $version = $rel['version'];
} else {
    $rel = array();
}

$name        = $pkg['name'];
$summary     = stripslashes($pkg['summary']);
$license     = $pkg['license'];
$description = stripslashes($pkg['description']);
$category    = $pkg['category'];

// Accounts data
$sth = $dbh->query("SELECT u.handle, u.name, u.email, u.showemail, u.wishlist, m.role".
                   " FROM maintains m, users u".
                   " WHERE m.package = $pacid".
                   " AND m.handle = u.handle");
$accounts  = '';
while ($sth->fetchInto($row)) {
    $accounts .= "<tr><td>{$row['name']}";
    if ($row['showemail'] == 1) {
        $accounts .= " &lt;<a href=\"mailto:{$row['email']}\">{$row['email']}</a>&gt;";
    }
    $accounts .= " ({$row['role']})";
    if (!empty($row['wishlist'])) {
        $accounts .= " [<a href=\"wishlist.php/{$row['handle']}\">wishlist</a>]";
    }
    $accounts .= " [<a href=\"account-info.php?handle={$row['handle']}\">details</a>]";
    $accounts .= "</td></tr>\n";
}

if (!$relid) {
    $releases = $dbh->getAll(
        "SELECT id, version, state, releasedate, releasenotes FROM releases".
        " WHERE package = $pacid ORDER BY releasedate DESC");
    $sth = $dbh->query("SELECT f.id AS id, f.release AS release,".
                       " f.platform AS platform, f.format AS format,".
                       " f.md5sum AS md5sum, f.basename AS basename,".
                       " f.fullpath AS fullpath, r.version AS version".
                       " FROM files f, releases r".
                       " WHERE f.package = $pacid AND f.release = r.id");
    while ($sth->fetchInto($row)) {
        $downloads[$row['version']][] = $row;
    }
}

// }}}
// {{{ page header

if ($version) {
    response_header("Package :: $name :: $version");
} else {
    response_header("Package :: $name");
}

html_category_urhere($category, true);

print "<h2 align=\"center\">$name";
if ($version) {
    print " $version";
}
print "</h2>\n";

// }}}
// {{{ "Package Information" box

$bb = new BorderBox("Package Information"); ?>

<table border="0" cellspacing="2" cellpadding="2" height="48" width="100%">
<tr>
    <th class="pack" width="20%">Summary</th>
    <td><?php print $summary;?></td>
</tr>
<tr>
    <th class="pack" width="20%">Maintainers</th>
    <td>
        <table border="0" cellspacing="1" cellpadding="1" width="100%">
        <?php print $accounts;?>
        </table>
    </td>
</tr>
<tr>
    <th class="pack" width="20%">License</th>
    <td><?php print get_license_link($license);?></td>
</tr>
<tr>
    <th class="pack" width="20%">Description</th>
    <td><?php print nl2br($description);?>&nbsp;</td>
</tr>
<?php

if ($relid) {
    print "<tr>\n";
    print "    <th class=\"pack\" width=\"20%\">Release Notes<br />Version $version</th>\n";
    print "     <td valign=\"top\">".nl2br($rel['releasenotes'])."</td>\n";
    print "</tr>\n";
}

?>
<tr>
    <td colspan="2" align="right">
<?php print_link("/package-edit.php?id=$pacid",
        make_image("edit.gif", "Edit package information")); ?>
&nbsp;
<?php print_link("/package-delete.php?id=$pacid",
        make_image("delete.gif", "Delete package")); ?>
    </td>
</tr>
</table>

<?php

$bb->end();

// }}}
// {{{ latest/cvs/changelog links

?>

<br />
<table border="0" cellspacing="3" cellpadding="3" height="48" width="100%" align="center">
<tr>
<?php

// CVS link
if (@is_dir(PHP_CVS_REPO_DIR . "/$name")) {
    $cvs_link = "[ " . make_link("http://cvs.php.net/cvs.php/pear/$name",
                                 'View Source Code in CVS', 'top')
    . " ] ";
    // XXX if "version" is set, add a release tag to the cvs link
} else {
    $cvs_link = '&nbsp;';
}

// Download link
$get_link = make_link("/get/$name", 'Download Latest');
$changelog_link = make_link("package-changelog.php?pacid=$pacid",
                            'ChangeLog');
?>
    <td width="33%" align="center">[ <?php print $get_link; ?> ]</td>
    <td width="33%" align="center"><?php print $cvs_link;?></td>
    <td width="33%" align="center">[ <?php print $changelog_link;?> ]</td>
</tr>
</table>

<br />

<?php

// }}}
// {{{ "Available Releases"

if (!$relid) {
    $bb = new BorderBox("Available Releases");
    if (count($releases) == 0) {
        print "<i>No releases for this package.</i>";
    } else {
        ?>
    <table border="0" cellspacing="0" cellpadding="3" width="100%">
        <th align="left">Version</th>
        <th align="left">State</th>
        <th align="left">Release Date</th>
        <th align="left">Downloads</th>
        <th></th>

    <?php

        foreach ($releases as $r) {
            print " <tr>\n";
            if (empty($r['state'])) {
                $r['state'] = 'devel';
            }
            $r['releasedate'] = substr($r['releasedate'], 0, 10);
            $downloads_html = '';
            foreach ($downloads[$r['version']] as $dl) {
                $downloads_html .= "<a href=\"/get/$dl[basename]\">".
                                   "$dl[basename]</a> (".sprintf("%.1fkB",filesize($dl['fullpath'])/1024.0).")<br />";
            }
            
            $link_changelog = "[ " . make_link("/package-changelog.php?pacid=".
                              "$pacid&release=" .
                              $r['version'], "Changelog")
                              . " ]";
            $href_release = $_SERVER['PHP_SELF'] . "?pacid=$pacid&version=".
                            urlencode($r['version']);

            printf("  <td><a href=\"%s\">%s</a></td>" .
                   "  <td>%s</td>" .
                   "  <td>%s</td>" .
                   "  <td>%s</td>" .
                   "  <td valign=\"middle\">%s</td>\n",                    
                   $href_release,
                   $r['version'],
                   $r['state'],
                   $r['releasedate'],
                   $downloads_html,
                   "<small>" . $link_changelog . "</small>\n"
                  );

            print " </tr>\n";
        }
    }
    print "</table>\n";
    $bb->end();

    print "<br /><br />\n";
}

// }}}
// {{{ "Dependencies"

$title = "Dependencies";
if ($relid) {
    $title .= " for version $version";
}
$bb = new Borderbox($title);

$query = "SELECT * FROM deps WHERE package = ?";
$params = array($pacid);
if ($relid) {
    $query .= " AND release = ?";
    $params[] = $relid;
}
$prh = $dbh->prepare($query);
$sth = $dbh->execute($prh, array($pacid, $relid));

if ($sth->numRows() == 0) {
    print "<i>No dependencies registered.</i>\n";
} else {
    $lastversion = '';
    $rel_trans = array(
        'lt' => 'older than %s',
        'le' => 'version %s or older',
        'eq' => 'version %s',
        'ne' => 'any version but %s',
        'gt' => 'newer than %s',
        'ge' => '%s or newer',
/*        'lt' => '<',
        'le' => '<=',
        'eq' => '=',
        'ne' => '!=',
        'gt' => '>',
        'ge' => '>=', */
        );
    $dep_type_desc = array(
        'pkg'    => 'PEAR Package',
        'ext'    => 'PHP Extension',
        'php'    => 'PHP Version',
        'prog'   => 'Program',
        'ldlib'  => 'Development Library',
        'rtlib'  => 'Runtime Library',
        'os'     => 'Operating System',
        'websrv' => 'Web Server',
        'sapi'   => 'SAPI Backend',
        );
    print "      <dl>\n";
    while ($row = $sth->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ($row['version'] != $lastversion) {
            print "\n";
            if ($lastversion) {
                print "       </dd>\n";
            }
            print "       <dt>Dependencies for version $row[version]:</dt>\n";
            print "       <dd>\n";
        } else {
            print "<br />\n";
        }
        print "        ";
        if (isset($rel_trans[$row['relation']])) {
            $rel = sprintf($rel_trans[$row['relation']], $row['version']);
            printf("%s: %s %s",
                   $dep_type_desc[$row['type']], $row['name'], $rel);
        } else {
            printf("%s: %s", $dep_type_desc[$row['type']], $row['name']);
        }
        $lastversion = $row['version'];
    }
    if ($lastversion) {
        print "\n       </dd>\n";
    }
    print "      </dl>\n";
}
$bb->end();

// }}}
// {{{ page footer

response_footer();

// }}}

?>
