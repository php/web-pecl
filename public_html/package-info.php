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
   | Authors: Pierre Joye <pierre@php.net>                                |
   +----------------------------------------------------------------------+
   $Id: package-info.php 317217 2011-09-23 20:33:57Z pajoye $
*/

include PECL_INCLUDE_DIR . '/pear-database-package.php';

$ar = explode('/', $_SERVER['REQUEST_URI']);
$arg_cnt =count($ar);
if ($arg_cnt == 3) {
    $package = $ar[2];
    $package_version = false;
} elseif ($arg_cnt == 4) {
    list(, ,$package, $package_version) = $ar;
}

$package = filter_var($package, FILTER_SANITIZE_STRING);
$package_version = filter_var($package_version, FILTER_SANITIZE_STRING);

if (!$package) {
    $_SERVER['REDIRECT_URL'] = $_SERVER['REQUEST_URI'];
    include 'error/404.php';
    exit();
}

if (is_numeric($package)) {
    $package = (int)$package;
}

// Package data
$package = package::info($package);
$release_id = false;
if (!empty($package_version)) {
    foreach ($package['releases'] as $ver => $release) {
        if ($ver == $package_version) {
            $release_id = $release['id'];
            break;
        }
    }
    if ($release_id === false) {
        die("not found");
    }
}

$dbh->setFetchmode(DB_FETCHMODE_OBJECT);

$unmaintained = ($package['unmaintained'] ? 'Y' : 'N');
$superseded   = ((bool) $package['new_package']  ? 'Y' : 'N');
$moved_out  = (!empty($package['new_channel'])  ? TRUE : FALSE);
if ($moved_out) {
	$superseded = 'Y';
}

$package_id = $package['packageid'];
$package_name = $package['name'];

// Accounts data
$maintainer_list = $dbh->getAll('SELECT u.handle, u.name, u.email, u.showemail, u.wishlist, m.role
                   FROM maintains m, users u
                    WHERE m.package = ' . $package['packageid'] . '
                   AND m.handle = u.handle', NULL, DB_FETCHMODE_OBJECT);

if (!$release_id) {
    $download_record = $dbh->getAll("SELECT
                        f.platform AS platform, f.format AS format,
                        f.md5sum AS md5sum, f.basename AS basename,
                        f.fullpath AS fullpath, r.version AS version
                        FROM files f, releases r
                        WHERE f.package = $package_id AND f.release = r.id", NULL, DB_FETCHMODE_OBJECT);
    $download_list = array();
    foreach ($download_record as $download) {
        $download->filesize = sprintf("%.1fkB",@filesize($dl['fullpath'])/1024.0);
        $download_list[$download->version] = $download;
    }
} else {
    $download_list = false;
}

if ($package_version) {
    $title = "Package :: $package_name :: $package_version";
} else {
    $title = "Package :: $package_name";
}

$dec_messages = array(
    'abandoned'    => 'This package is not maintained anymore and has been superseded.',
    'superseded'   => 'This package has been superseded, but is still maintained for bugs and security fixes.',
    'unmaintained' => 'This package is not maintained, if you would like to take over please go to <a href="/takeover.php">this page</a>.'
);

$dec_table = array(
    'abandoned'    => array('superseded' => 'Y', 'unmaintained' => 'Y'),
    'superseded'   => array('superseded' => 'Y', 'unmaintained' => 'N'),
    'unmaintained' => array('superseded' => 'N', 'unmaintained' => 'Y'),
);

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
    $warning_msg = $dec_messages[$apply_rule];

    if ($package['new_channel'] == PEAR_CHANNELNAME) {
        $warning_msg .= '  Use <a href="/package/' . $package['new_package'] .
            '">' . htmlspecialchars($package['new_package']) . '</a> instead.';
    } elseif ($package['new_channel']) {
        $warning_msg .= '  Package has moved to channel <a href="' . $package['new_channel'] .
            '">' . htmlspecialchars($package['new_channel']) . '</a>, package ' .
            $package['new_package'] . '.';
    }
} else {
    $warning_msg = false;
}

$release_list = $package['releases'];

function dep_to_human_text($dep) {
    global $dbh;
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
        );

    $dep_text = '';
    if ($dep['type'] == 'pkg_pecl') {
            $dep['name'] = sprintf('<a href="/package/%s">%s</a>', $dep['name'], $dep['name']);
    }

    if (isset($rel_trans[$dep['relation']])) {
        $rel = sprintf($rel_trans[$dep['relation']], $dep['version']);
        $dep_text .= sprintf("%s: %s %s",
                          $dep_type_desc[$dep['type']], $dep['name'], $rel);
    } else {
        $dep_text .= sprintf("%s: %s", $dep_type_desc[$dep['type']], $dep['name']);
    }
    if ($dep['optional']) {
        $dep_text .= ' (optional)';
    }
    return $dep_text;
}

// Check if there are too much things to show
$too_many_releases = false;
if (count ($release_list) > 3) {
    $too_many_releases = true;
}

if (count($package['releases'])) {
    $dependency_list = array();

    // Loop per version
    $count = 3;
    foreach ($release_list as $r_version => $rel) {
        if (!empty($package_version) && $r_version != $package_version) {
            continue;
        }

        if ($count-- < 1) {
            break;
        }

        $deps = $package['releases'][$r_version]['deps'];
        $dependency_list[$r_version] =  $package['releases'][$r_version]['deps'];
        $dep = array();
        if (count($deps) > 0) {
            foreach ($deps as $row) {
                // Print link if it's a PECL package and it's in the db
                if ($row['type'] == 'pkg') {
                    $dep_package_type = $dbh->getOne("SELECT package_type FROM packages WHERE name = '" . $row['name'] . "'");
                    if ($dep_package_type == 'pecl') {
                        $row['type'] = 'pkg_pecl';
                    }
                }
                $dep[] = $row;
            }
        }
        $dependency_list[$r_version] = $dep;
    }
}

$dependant_list = package::getDependants($package_name);

$data = array(
    'package' => $package,
    'maintainer_list' => $maintainer_list,
    'dependency_list' => $dependency_list,
    'dependant_list' => $dependant_list,
    'release_list' => $release_list,
    'download_list' => $download_list,
    'too_many_releases' => $too_many_releases,
    'release_id' => $release_id,
    'package_version' => $package_version,
    'warning_msg' =>$warning_msg,
);

if ($release_id) {
    $filename = strtolower($package_name) . '-' . $package_version . '.html';
} else {
    $filename = strtolower($package_name) . '.html';
}

$page = new PeclPage();
$page->title = $title;
$page->setTemplate(PECL_TEMPLATE_DIR . '/package-info.html');
$page->addData($data);
$page->saveTo(PECL_STATIC_HTML_DIR . '/package/' . $filename);
$page->render();
echo $page->html;
