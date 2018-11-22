<?php

/*
  +----------------------------------------------------------------------+
  | The PECL website                                                     |
  +----------------------------------------------------------------------+
  | Copyright (c) 1999-2018 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | https://php.net/license/3_01.txt                                     |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors: Martin Jansen <mj@php.net>                                  |
  |          Tomas V.V.Cox <cox@idecnet.com>                             |
  +----------------------------------------------------------------------+
*/

use App\BorderBox;
use App\Utils\Licenser;
use App\PackageDll;
use App\Repository\UserRepository;

$licenser = new Licenser();
$packageDll = new PackageDll($config->get('tmp_dir'));

$package = filter_input(INPUT_GET, 'package', FILTER_SANITIZE_STRING);
$version = filter_input(INPUT_GET, 'version', FILTER_SANITIZE_STRING);
$relid = filter_input(INPUT_GET, FILTER_VALIDATE_INT);

if (!$package) {
    $_SERVER['REDIRECT_URL'] = $_SERVER['REQUEST_URI'];
    header('HTTP/1.0 404 Not Found');
    include 'error/404.php';
    exit();
}

if (is_numeric($package)) {
    $package = (int)$package;
}

// Package data
$pkg = $packageEntity->info($package);

if (!empty($version)) {
    foreach ($pkg['releases'] as $ver => $release) {
        if ($ver == $version) {
            $relid = $release['id'];
            break;
        }
    }
}

if (empty($package) || !isset($pkg['name'])) {
    $_SERVER['REDIRECT_URL'] = $_SERVER['REQUEST_URI'];
    header('HTTP/1.0 404 Not Found');
    include 'error/404.php';
    exit();
}

$name         = $pkg['name'];
$summary      = stripslashes($pkg['summary']);
$license      = $pkg['license'];
$description  = stripslashes($pkg['description']);
$category     = $pkg['category'];
$homepage     = $pkg['homepage'];
$pacid        = $pkg['packageid'];
$cvs_link     = $pkg['cvs_link'];
$doc_link     = $pkg['doc_link'];
$bug_link     = $pkg['bug_link'];
$unmaintained = ($pkg['unmaintained'] ? 'Y' : 'N');
$superseded   = ((bool) $pkg['new_package']  ? 'Y' : 'N');
$moved_out  = (!empty($pkg['new_channel'])  ? TRUE : FALSE);
if ($moved_out) {
    $superseded = 'Y';
}

// Maintainers data
$userRepository = new UserRepository($database);

$accounts  = '';
foreach ($userRepository->findMaintainersByPackageId($pacid) as $row) {
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
    $downloads = [];

    $statement = $database->run("SELECT f.id AS `id`, f.release AS `release`,".
                       " f.platform AS platform, f.format AS format,".
                       " f.md5sum AS md5sum, f.basename AS basename,".
                       " f.fullpath AS fullpath, r.version AS version".
                       " FROM files f, releases r".
                       " WHERE f.package = :package_id AND f.release = r.id", [
                           ':package_id' => $pacid
                       ]);
    foreach ($statement->fetchAll() as $row) {
        $downloads[$row['version']][] = $row;
    }
}

// page header
if ($version) {
    response_header("Package :: $name :: $version");
} else {
    response_header("Package :: $name");
}

html_category_urhere($pkg['categoryid'], true);
if ($relid) {
    echo ' :: <a href="/package/'.$name.'">'.$name.'</a>';
}

print "<h2 align=\"center\">$name";
if ($version) {
    print " $version";
}

print "</h2>\n";

// Supeseded checks
$dec_messages = [
    'abandoned'    => 'This package is not maintained anymore and has been superseded.',
    'superseded'   => 'This package has been superseded, but is still maintained for bugs and security fixes.',
    'unmaintained' => 'This package is not maintained, if you would like to take over please go to <a href="/takeover.php">this page</a>.'
];

$dec_table = [
    'abandoned'    => ['superseded' => 'Y', 'unmaintained' => 'Y'],
    'superseded'   => ['superseded' => 'Y', 'unmaintained' => 'N'],
    'unmaintained' => ['superseded' => 'N', 'unmaintained' => 'Y'],
];

$apply_rule = null;
foreach ($dec_table as $rule => $conditions) {
    $match = true;
    foreach ($conditions as $condition => $value) {
        if ($$condition != $value) {
            $match = false;
            break;
        }
    }
    if ($match) {
        $apply_rule = $rule;
    }
}

if (!is_null($apply_rule) && isset($dec_messages[$apply_rule])) {
    $str  = '<div class="warnings">';
    $str .= $dec_messages[$apply_rule];

        if ($pkg['new_channel'] == $config->get('host')) {
            $str .= '  Use <a href="/package/' . $pkg['new_package'] .
                '">' . htmlspecialchars($pkg['new_package']) . '</a> instead.';
        } elseif ($pkg['new_channel']) {
            $str .= '  Package has moved to channel <a href="' . $pkg['new_channel'] .
                '">' . htmlspecialchars($pkg['new_channel']) . '</a>, package ' .
                $pkg['new_package'] . '.';
        }

    $str .= '</div>';
    echo $str;
}

// Package Information box
$bb = new BorderBox("Package Information", "90%", "", 2, true);

$bb->horizHeadRow("Summary", $summary);
$bb->horizHeadRow("Maintainers", $accounts);
$bb->horizHeadRow("License", $licenser->getHtml($license));
$bb->horizHeadRow("Description", nl2br($description));

if (!empty($homepage)) {
    $bb->horizHeadRow("Homepage", '<a href="'.$homepage.'">'.$homepage.'</a>');
}

if ($relid) {
    // Find correct version for given release id
    foreach ($pkg['releases'] as $r_version => $release) {
        if ($release['id'] != $relid) {
            continue;
        }

        $bb->horizHeadRow("Release notes<br />Version " . $version . "<br />(" . $release['state'] . ")", nl2br($release['releasenotes']));
        break;
    }
}

if (!empty($auth_user)) {
    $bb->fullRow("<div align=\"right\">" .
                 '<a href="/package-edit.php?id='.$pacid.'">'.
                           '<img src="/img/edit.gif" alt="Edit package information"></a>'.
                 ($auth_user->isAdmin() ? '&nbsp;<a href="/package-delete.php?id='.$pacid.'">'.
                                      '<img src="/img/delete.gif" alt="Delete package"></a>' : '').
                 '&nbsp;[<a href="/admin/package-maintainers.php?pid='.$pacid.'">Edit maintainers</a>]</div>');
}

$bb->end();

// latest/cvs/changelog links
?>

<br />
<table border="0" cellspacing="3" cellpadding="3" height="48" width="90%" align="center">
<tr>
<?php
$get_link = '<a href="/get/'.$name.'">Latest Tarball</a>';
if ($version) {
    $changelog_link = '<a href="/package-changelog.php?package='.$pkg['name'].'&amp;release='.$version.'">Changelog</a>';
} else {
    $changelog_link = '<a href="/package-changelog.php?package='.$pkg['name'].'">Changelog</a>';
}
$stats_link = '<a href="/package-stats.php?pid='.$pacid.'&amp;rid=&amp;cid='.$pkg['categoryid'].'">View Statistics</a>';
?>
    <td align="center">[ <?php print $get_link; ?> ]</td>
    <td align="center">[ <?php print $changelog_link; ?> ]</td>
    <td align="center">[ <?php print $stats_link; ?> ]</td>
</tr>
<tr>
<td align="center">
<?php
if (!empty($cvs_link)) {
    print '[ <a href="'.$cvs_link.'" target="_blank">Browse Source</a> ]';
}
print '&nbsp;</td>';

if (!empty($bug_link)) {
    print '<td align="center">[ <a href="'.$bug_link.'">Package Bugs</a> ]</td>';
} else {
    print '<td align="center">[ <a href="https://bugs.php.net/search.php?cmd=display&status=Open&package_name[]='.$pkg['name'].'">Package Bugs</a> ]</td>';
}
if (!empty($doc_link)) {
    print '<td align="center">[ <a href="'.$doc_link.'">View Documentation</a> ]</td>';
} else {
    print '<td />';
}
?>
</tr>
<?php
if (empty($bug_link)) {
?>
<tr>
    <td align="center">[ <a href="https://bugs.php.net/report.php?package=<?= $pkg['name']; ?>">Report new bug</a> ]</td>
</tr>
<?php
}
?>
</table>

<br />

<?php

// Available Releases
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

                $urls = $packageDll->getDllDownloadUrls($pkg['name'], $r_version, $pkg['releases'][$r_version]['releasedate']);
                if ($urls) {
                    $downloads_html .= "&nbsp;&nbsp;<a href=\"/package/$pkg[name]/$r_version/windows\">"
                                    . "<img src=\"/img/windows-icon.png\" />DLL</a>";
                }
            }

            $link_changelog = '<small>[<a href="/package-changelog.php?package='.$pkg['name'].'&release='.$r_version.'">Changelog</a>]</small>';

            $href_release = "/package/" . $pkg['name'] . "/" . $r_version;

            $bb->horizHeadRow(
                '<a href="'.$href_release.'">'.$r_version.'</a>',
                $r['state'],
                $r['releasedate'],
                $downloads_html,
                $link_changelog
            );

        }
    }

    $bb->end();

    print "<br /><br />\n";
}

// Dependencies
$title = "Dependencies";
if ($relid) {
    $title .= " for release $version";
}
$bb = new BorderBox($title, "90%", "", 2, true);

$rels = $pkg['releases'];

// Check if there are too much things to show
$too_much = false;
if (count ($rels) > 3) {
    $too_much = true;
    $rels = array_slice($rels, 0, 3);
}

if ($statement->rowCount() == 0) {
    print "<i>No releases yet.</i>";
} else {
    $rel_trans = [
        'lt' => 'older than %s',
        'le' => 'version %s or older',
        'eq' => 'version %s',
        'ne' => 'any version but %s',
        'gt' => 'newer than %s',
        'ge' => '%s or newer',
    ];
    $dep_type_desc = [
        'pkg'         => 'PEAR Package',
        'pkg_pecl'    => 'PECL Package',
        'ext'    => 'PHP Extension',
        'php'    => 'PHP Version',
        'prog'   => 'Program',
        'ldlib'  => 'Development Library',
        'rtlib'  => 'Runtime Library',
        'os'     => 'Operating System',
        'websrv' => 'Web Server',
        'sapi'   => 'SAPI Backend',
    ];

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

        $deps = $pkg['releases'][$r_version]['deps'];

        if (count($deps) > 0) {
            foreach ($deps as $row) {
                // we have PEAR installer deps both with the name of PEAR Installer and PEAR, make it consistent here until it is fixed in the db
                if ($row['name'] == 'PEAR Installer') {
                    $row['name'] = 'PEAR';
                }

                // fix up wrong dep types here, until it is fixed in the db, we only have the pecl packages in the db now
                if ($row['type'] == 'pkg' && $database->run("SELECT id, package_type FROM packages WHERE name = ?", [$row['name']])->fetch()) {
                    $row['type'] = 'pkg_pecl';
                }

                if ($row['type'] == 'pkg_pecl') {
                    $dep_name_html = sprintf('<a href="/package/%s">%s</a>', $row['name'], $row['name']);
                } elseif ($row['type'] == 'pkg') {
                    $dep_name_html = sprintf('<a href="https://pear.php.net/package/%s">%s</a>', $row['name'], $row['name']);
                } else {
                    $dep_name_html = $row['name'];
                }

                if (isset($rel_trans[$row['relation']])) {
                    $rel = sprintf($rel_trans[$row['relation']], $row['version']);
                    $dep_text .= sprintf("%s: %s %s",
                                          $dep_type_desc[$row['type']], $dep_name_html, $rel);
                } else {
                    $dep_text .= sprintf("%s: %s", $dep_type_desc[$row['type']], $dep_name_html);
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

// Dependants
$dependants = $packageEntity->getDependants($name);

if (count($dependants) > 0) {

    echo "<br /><br />";
    $bb = new BorderBox("Packages that depend on " . $name);

    foreach ($dependants as $dep) {
        $bb->plainRow('<a href="/package/"'.$dep['p_name'].'">'.$dep['p_name'].'</a>');
    }

    $bb->end();
}

response_footer();
