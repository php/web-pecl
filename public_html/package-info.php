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
   | Authors: Martin Jansen <mj@php.net>                                  |
   |          Tomas V.V.Cox <cox@idecnet.com>                             |
   +----------------------------------------------------------------------+
   $Id$
*/

require_once "Damblan/URL.php";
$site = new Damblan_URL;

// {{{ setup, queries

$params = array("package|pacid" => "", "version" => "");
$site->getElements($params);

$pacid = $params['package|pacid'];

// Package data
if (!empty($pacid)) {
    $pkg = package::info($pacid);
}

$version = $params['version'];
$relid = null;
if (!empty($version)) {
    foreach ($pkg['releases'] as $ver => $release) {
        if ($ver == $version) {
            $relid = $release['id'];
            break;
        }
    }
} else {
    $relid = (isset($_GET['relid'])) ? (int) $_GET['relid'] : null;
}

if (empty($pacid) || !isset($pkg['name'])) {
    // Let's see if $pacid is a PEAR package
    if (!isset($pkg['name'])) {
        $pkg_name = package::info($pacid, 'name', true);
        if (!empty($pkg_name)) {
            header('HTTP/1.0 301 Moved Permanently');
            header('Location: http://pear.php.net/package/' . $pkg_name);
            header('Connection: close');
            exit();
        }
    }

    $_SERVER['REDIRECT_URL'] = $_SERVER['REQUEST_URI'];
    include 'error/404.php';
    exit();
}

$dbh->setFetchmode(DB_FETCHMODE_ASSOC);

$name        = $pkg['name'];
$summary     = stripslashes($pkg['summary']);
$license     = $pkg['license'];
$description = stripslashes($pkg['description']);
$category    = $pkg['category'];
$homepage    = $pkg['homepage'];
$pacid       = $pkg['packageid'];
$cvs_link    = $pkg['cvs_link'];
$doc_link    = $pkg['doc_link'];

// Accounts data
$sth = $dbh->query("SELECT u.handle, u.name, u.email, u.showemail, u.wishlist, m.role".
                   " FROM maintains m, users u".
                   " WHERE m.package = $pacid".
                   " AND m.handle = u.handle");
$accounts  = '';
while ($row = $sth->fetchRow()) {
    $accounts .= "{$row['name']}";
    if ($row['showemail'] == 1) {
        $accounts .= " &lt;<a href=\"mailto:{$row['email']}\">{$row['email']}</a>&gt;";
    }
    $accounts .= " ({$row['role']})";
    if (!empty($row['wishlist'])) {
        $accounts .= " [<a href=\"/wishlist.php/{$row['handle']}\">wishlist</a>]";
    }
    $accounts .= " [<a href=\"/user/{$row['handle']}\">details</a>]<br />";
}

if (!$relid) {
    $downloads = array();

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

html_category_urhere($pkg['categoryid'], true);

print "<h2 align=\"center\">$name";
if ($version) {
    print " $version";
}

print "</h2>\n";

// }}}
// {{{ "Package Information" box

$bb = new BorderBox("Package Information", "90%", "", 2, true);

$bb->horizHeadRow("Summary", $summary);
$bb->horizHeadRow("Maintainers", $accounts);
$bb->horizHeadRow("License", get_license_link($license));
$bb->horizHeadRow("Description", nl2br($description));

if (!empty($homepage)) {
    $bb->horizHeadRow("Homepage", make_link($homepage));
}

if ($relid) {
    // Find correct version for given release id
    foreach ($pkg['releases'] as $r_version => $release) {
        if ($release['id'] != $relid) {
            continue;
        }

        $bb->horizHeadRow("Release notes<br />Version" . $version, nl2br($release['releasenotes']));
        break;
    }
}

if (!empty($_COOKIE['PEAR_USER'])) {
    $bb->fullRow("<div align=\"right\">" .
                 make_link("/package-edit.php?id=$pacid",
                           make_image("edit.gif", "Edit package information")) .
                 "&nbsp;" . make_link("/package-delete.php?id=$pacid",
                                      make_image("delete.gif", "Delete package")) .
                 "&nbsp;[" . make_link("/admin/package-maintainers.php?pid=$pacid",
                                       "Edit maintainers") .
                 "]</div>");
}

$bb->end();

// }}}
// {{{ latest/cvs/changelog links

?>

<br />
<table border="0" cellspacing="3" cellpadding="3" height="48" width="90%" align="center">
<tr>
<?php
$get_link = make_link("/get/$name", 'Download Latest');
if ($version) {
    $changelog_link = make_link("/package-changelog.php?package=" .
                                $pkg['name'] . '&amp;release=' . $version,
                                'ChangeLog');
} else {
    $changelog_link = make_link("/package-changelog.php?package=" . $pkg['name'],
                                'ChangeLog');
}
$stats_link = make_link("/package-stats.php?pid=" . $pacid . "&amp;rid=&amp;cid=" . $pkg['categoryid'],
                        "View package statistics");
?>
    <td align="center">[ <?php print $get_link; ?> ]</td>
    <td align="center">[ <?php print $changelog_link; ?> ]</td>
    <td align="center">[ <?php print $stats_link; ?> ]</td>
</tr>
<tr>
<td align="center">
<?php
if (!empty($cvs_link)) {
    print '[ ' . make_link($cvs_link, 'CVS Web', 'top') . ' ]';
}
print '&nbsp;</td>';
print '<td align="center">[ ' . make_bug_link($pkg['name']) . ' ]</td>';
if (!empty($doc_link)) {
    print '<td align="center">[ ' . make_link($doc_link, "View documentation") . ' ]</td>';
} else {
    print '<td />';
}
?>
</tr>
</table>

<br />

<?php

// }}}
// {{{ "Available Releases"

if (!$relid) {
    $bb = new BorderBox("Available Releases", "90%", "", 5, true);

    if (count($pkg['releases']) == 0) {
        print "<i>No releases for this package.</i>";
    } else {
        $bb->headRow("Version", "State", "Release Date", "Downloads", "");

        foreach ($pkg['releases'] as $r_version => $r) {
            if (empty($r['state'])) {
                $r['state'] = 'devel';
            }
            $r['releasedate'] = substr($r['releasedate'], 0, 10);
            $dl = $downloads[$r_version];
            $downloads_html = '';
            foreach ($downloads[$r_version] as $dl) {
                $downloads_html .= "<a href=\"/get/$dl[basename]\">".
                                   "$dl[basename]</a> (".sprintf("%.1fkB",@filesize($dl['fullpath'])/1024.0).")";
            }

            $link_changelog = "<small>[" . make_link("/package-changelog.php?package=" .
                                                     $pkg['name'] . "&release=" .
                                                     $r_version, "Changelog")
                . "]</small>";

            $href_release = "/package/" . $pkg['name'] . "/" . $r_version;

            $bb->horizHeadRow(make_link($href_release, $r_version), $r['state'],
                          $r['releasedate'], $downloads_html, $link_changelog);

        }
    }

    $bb->end();

    print "<br /><br />\n";
}

// }}}
// {{{ "Dependencies"

$title = "Dependencies";
if ($relid) {
    $title .= " for release $version";
}
$bb = new Borderbox($title, "90%", "", 2, true);

$rels =& $pkg['releases'];

// Check if there are too much things to show
$too_much = false;
if (count ($rels) > 3) {
    $too_much = true;
    $rels = array_slice($rels, 0, 3);
}

if ($sth->numRows() == 0) {
    print "<i>No releases yet.</i>";
} else {
    $rel_trans = array(
        'lt' => 'older than %s',
        'le' => 'version %s or older',
        'eq' => 'version %s',
        'ne' => 'any version but %s',
        'gt' => 'newer than %s',
        'ge' => '%s or newer',
/*      'lt' => '<',
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

    // Loop per version
    foreach ($rels as $r_version => $rel) {
        $dep_text = "";

        if (!empty($version) && $r_version != $version) {
            continue;
        }
        if (empty($version)) {
            $title = "Release " . $r_version . ":";
        } else {
            $title = "";
        }

        $deps =& $pkg['releases'][$r_version]['deps'];

        if (count($deps) > 0) {
            foreach ($deps as $row) {
                // Print link if it's a PEAR package and it's in the db
                if ($row['type'] == 'pkg' AND $pid = $dbh->getOne(sprintf("SELECT id FROM packages WHERE name = '%s'", $row['name']))) {
                    $row['name'] = sprintf('<a href="/package/%s">%s</a>', $row['name'], $row['name']);
                }

                if (isset($rel_trans[$row['relation']])) {
                    $rel = sprintf($rel_trans[$row['relation']], $row['version']);
                    $dep_text .= sprintf("%s: %s %s",
                                          $dep_type_desc[$row['type']], $row['name'], $rel);
                } else {
                    $dep_text .= sprintf("%s: %s", $dep_type_desc[$row['type']], $row['name']);
                }
                $dep_text .= "<br />";
            }
            $bb->horizHeadRow($title, $dep_text);

        } else {
            $bb->horizHeadRow($title, "No dependencies registered.");
        }
    }
    if ($too_much && empty($version)) {
        $bb->fullRow("Dependencies for older releases can be found on the release overview page.");
    }
}
$bb->end();

// }}}
// {{{ Dependants

$dependants = package::getDependants($name);

if (count($dependants) > 0) {

    echo "<br /><br />";
    $bb = new BorderBox("Packages that depend on " . $name);

    foreach ($dependants as $dep) {
        $bb->plainRow(make_link("/package/" . $dep['p_name'], $dep['p_name']));
    }

    $bb->end();
}

// }}}
// {{{ page footer

response_footer();

// }}}
?>
