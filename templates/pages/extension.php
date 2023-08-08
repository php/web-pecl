<?php $this->extend('layout.php', ['title' => 'Package :: '.$package['name'].($version ? ' '.$version : '').($windows ? ' for Windows' : '')]) ?>

<?php $this->start('content') ?>

<?= $breadcrumbs ?>

<?php if (!empty($releaseId)): ?>
    :: <a href="/package/<?= $this->e($package['name']) ?>"><?= $this->e($package['name']) ?></a>
<?php else: ?>
    :: <?= $this->e($package['name']) ?>
<?php endif ?>

<?php if (!empty($version)): ?>
    :: <a href="/package/<?= $this->e($package['name']) ?>/<?= $this->e($version) ?>"><?= $this->e($version) ?></a>
<?php endif ?>

<?php if (!empty($windows)): ?>
    :: Windows
<?php endif ?>

<h2 style="text-align:center">
    <?= $this->e($package['name']) ?>
    <?= !empty($version) ? $this->e($version) : '' ?>
    <?= $windows ? ' for Windows' : '' ?>
</h2>

<?php if ($superseded || $unmaintained): ?>
    <div class="warnings">
        <?php if ($superseded && $unmaintained): ?>
            This package is not maintained anymore and has been superseded.
        <?php elseif ($superseded && !$unmaintained): ?>
            This package has been superseded, but is still maintained for bugs and security fixes.
        <?php elseif (!$superseded && $unmaintained): ?>
            This package is not maintained, if you would like to take over please go to <a href="/takeover.php">this page</a>.
        <?php endif ?>

        <?php if ($package['new_channel'] === $host): ?>
            Use <a href="/package/<?= $this->noHtml($package['new_package']) ?>">
                <?= $this->noHtml($package['new_package']) ?></a> instead.
        <?php elseif ($package['new_channel']): ?>
            Package has moved to channel <a href="<?= $this->e($package['new_channel']) ?>">
            <?= $this->e($package['new_channel']) ?></a>
            <?php if ($package['new_package']): ?>
                package <?= $this->e($package['new_package']) ?>.
            <?php endif ?>
        <?php endif ?>
    </div>
<?php endif ?>

<table cellpadding="0" cellspacing="1" style="width: 90%; border: 0px;">
    <tr>
        <td style="background-color: #000000">
            <table cellpadding="2" cellspacing="1" style="width: 100%; border: 0px;">
                <tr style="background-color: #CCCCCC;">
                    <th colspan="2">Package Information</th>
                </tr>

                <tr>
                    <th valign="top" style="background-color: #cccccc">Summary</th>
                    <td valign="top" style="background-color: #e8e8e8"><?= $this->e($package['summary']) ?></td>
                </tr>

                <tr>
                    <th valign="top" style="background-color: #cccccc">Maintainers</th>
                    <td valign="top" style="background-color: #e8e8e8">
                        <?php foreach ($maintainers as $maintainer): ?>
                            <?= $this->e($maintainer['name']) ?>
                            <?php if ((1 === (int) $maintainer['showemail']) && (1 === (int) $maintainer['active'])): ?>
                                &lt;<a href="/account-mail.php?handle=<?= $this->noHtml($maintainer['handle']) ?>">
                                    <?= $this->e(str_replace(["@", "."], [" at ", " dot "], $maintainer['email'])) ?>
                                </a>&gt;
                            <?php endif ?>
                            (<?= $this->e($maintainer['role']) ?>)
                            <?php if (1 !== (int) $maintainer['active']): ?>
                                [inactive]
                            <?php endif ?>
                            <?php if (!empty($maintainer['wishlist'])): ?>
                                [<a href="/wishlist.php/<?= $this->e($maintainer['handle']) ?>">wishlist</a>]
                            <?php endif ?>
                            [<a href="/user/<?= $this->e($maintainer['handle']) ?>">details</a>]<br>
                        <?php endforeach ?>
                    </td>
                </tr>

                <tr>
                    <th valign="top" style="background-color: #cccccc">License</th>
                    <td valign="top" style="background-color: #e8e8e8"><?= $license ?></td>
                </tr>

                <tr>
                    <th valign="top" style="background-color: #cccccc">Description</th>
                    <td valign="top" style="background-color: #e8e8e8"><?= $this->nl2br($this->e($package['description'])) ?></td>
                </tr>

                <?php if (!empty($package['homepage'])): ?>
                    <tr>
                        <th valign="top" style="background-color: #cccccc">Homepage</th>
                        <td valign="top" style="background-color: #e8e8e8">
                            <a href="<?= $this->e($package['homepage']) ?>">
                                <?= $this->e($package['homepage']) ?>
                            </a>
                        </td>
                    </tr>
                <?php endif ?>

                <?php if ($release): ?>
                    <tr>
                        <th valign="top" style="background-color: #cccccc">
                            Release notes<br>
                            Version <?= $this->e($version) ?><br>
                            (<?= $this->e($release['state']) ?>)
                        </th>
                        <td valign="top" style="background-color: #e8e8e8">
                            <?= $this->nl2br($this->noHtml($release['releasenotes'])) ?>
                        </td>
                    </tr>
                <?php endif ?>

                <?php if (!empty($authUser)): ?>
                    <tr>
                        <td style="background-color: #e8e8e8" colspan="2">
                            <div style="text-align:right">
                                <a href="/package-edit.php?id=<?= $this->noHtml($package['packageid']) ?>">
                                    <img src="/img/edit.gif" alt="Edit package information">
                                </a>
                                <?php if ($authUser->isAdmin()): ?>
                                    &nbsp;<a href="/package-delete.php?id=<?= $this->noHtml($package['packageid']) ?>">
                                        <img src="/img/delete.gif" alt="Delete package"></a>
                                <?php endif ?>
                                &nbsp;[<a href="/admin/package-maintainers.php?pid=<?= $this->noHtml($package['packageid']) ?>">
                                    Edit maintainers
                                </a>]
                            </div>
                        </td>
                    </tr>
                <?php endif ?>

            </table>
        </td>
    </tr>
</table>

<?php if ($windows && $version): ?>
    <div>&nbsp;</div>
    <table cellpadding="0" cellspacing="1" style="width: 90%; border: 0px;">
        <tr>
            <td style="background-color: #000000">
                <table cellpadding="2" cellspacing="1" style="width: 100%; border: 0px;">
                    <tr style="background-color: #CCCCCC;">
                        <th colspan="2">DLL List</th>
                    </tr>

                    <?php if (0 === count($urls) || !$urls): ?>
                        <tr>
                            <td style="background-color: #e8e8e8" colspan="2">
                                <i>No DLL available</i>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($urls as $desc => $set): ?>
                            <tr>
                                <th valign="top" style="background-color: #cccccc">PHP <?= $this->e($desc) ?></th>
                                <td valign="top" style="background-color: #e8e8e8">
                                    <?php foreach ($set as $url): ?>
                                        <a href="<?= $this->e($url) ?>">
                                            <?= $this->e($this->makeNiceLinkNameFromZipName($url)) ?>
                                        </a>
                                        <br>
                                    <?php endforeach ?>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    <?php endif ?>

                </table>
            </td>
        </tr>
    </table>
    <p>In case of missing DLLs, consider to contact the <a href="https://windows.php.net/team/">PHP for Windows Team</a>.</p>
<?php endif ?>

<br>
<table border="0" cellspacing="3" cellpadding="3" height="48" width="90%" align="center">
    <tr>
        <td align="center">[ <a href="/get/<?= $this->noHtml($package['name']) ?>">Latest Tarball</a> ]</td>
        <td align="center">[
            <?php if ($version): ?>
                <a href="/package-changelog.php?package=<?= $this->noHtml($package['name']) ?>&amp;release=<?= $this->noHtml($version) ?>">
                    Changelog
                </a>
            <?php else: ?>
                <a href="/package-changelog.php?package=<?= $this->noHtml($package['name']) ?>">
                    Changelog
                </a>
            <?php endif ?>
        ]</td>
        <td align="center">
            [ <a href="/package-stats.php?pid=<?= $this->noHtml($package['packageid']) ?>&amp;rid=&amp;cid=<?= $this->noHtml($package['categoryid']) ?>">
                View Statistics
            </a> ]
        </td>
    </tr>
    <tr>
        <td align="center">
            <?php if (!empty($package['cvs_link'])): ?>
                [ <a href="<?= $this->e($package['cvs_link']) ?>" target="_blank">Browse Source</a> ]
            <?php else: ?>
                &nbsp;
            <?php endif ?>
        </td>

        <td align="center">
            <?php if (!empty($package['bug_link'])): ?>
                [ <a href="<?= $this->e($package['bug_link']) ?>">Package Bugs</a> ]
            <?php else: ?>
                [ <a href="https://bugs.php.net/search.php?cmd=display&status=Open&package_name[]=<?= $this->noHtml($package['name']) ?>">
                    Package Bugs
                </a> ]
            <?php endif ?>
        </td>

        <td align="center">
            <?php if (!empty($package['doc_link'])): ?>
                [ <a href="<?= $this->e($package['doc_link']) ?>">
                    View Documentation
                </a> ]
            <?php endif ?>
        </td>
    </tr>

    <?php if (empty($package['bug_link'])): ?>
        <tr>
            <td align="center">
                [ <a href="https://bugs.php.net/report.php?package=<?= $this->noHtml($package['name']) ?>">
                Report new bug
                </a> ]
            </td>
        </tr>
    <?php endif ?>
</table>

<br>

<?php if (!$releaseId): ?>
    <table cellpadding="0" cellspacing="1" style="width: 90%; border: 0px;">
        <tr>
            <td style="background-color: #000000">
                <table cellpadding="2" cellspacing="1" style="width: 100%; border: 0px;">
                    <tr style="background-color: #CCCCCC;">
                        <th colspan="5">Available Releases</th>
                    </tr>

    <?php if (0 === count($package['releases'])): ?>
        <tr>
            <td style="background-color: #e8e8e8" colspan="2">
                <i>No releases for this package.</i>
            </td>
        </tr>
    <?php else: ?>
        <tr>
            <th valign="top" style="background-color: #ffffff">Version</th>
            <th valign="top" style="background-color: #ffffff">State</th>
            <th valign="top" style="background-color: #ffffff">Release Date</th>
            <th valign="top" style="background-color: #ffffff">Downloads</th>
            <th valign="top" style="background-color: #ffffff">&nbsp;</th>
        </tr>

        <?php foreach ($package['releases'] as $releaseVersion => $r): ?>
            <?php
            if (empty($r['state'])) {
                $r['state'] = 'devel';
            }
            $r['releasedate'] = substr($r['releasedate'], 0, 10);
            $downloads_html = '';
            foreach ($downloads[$releaseVersion] as $dl) {
                $downloads_html .= "<a href=\"/get/$dl[basename]\">".
                                   "$dl[basename]</a> (".sprintf("%.1fkB",@filesize($dl['fullpath'])/1024.0).")";

                $urls = $packageDll->getDllDownloadUrls($package['name'], $releaseVersion, $package['releases'][$releaseVersion]['releasedate']);
                if ($urls) {
                    $downloads_html .= "&nbsp;&nbsp;<a href=\"/package/$package[name]/$releaseVersion/windows\">"
                                    . "<img src=\"/img/windows-icon.png\" />DLL</a>";
                }
            }
            ?>

            <tr>
                <th valign="top" style="background-color: #cccccc">
                    <a href="/package/<?= $this->noHtml($package['name'].'/'.$releaseVersion) ?>"><?= $this->noHtml($releaseVersion) ?></a>
                </th>
                <td valign="top" style="background-color: #e8e8e8"><?= $this->e($r['state']) ?></td>
                <td valign="top" style="background-color: #e8e8e8"><?= $this->e($r['releasedate']) ?></td>
                <td valign="top" style="background-color: #e8e8e8"><?= $downloads_html ?></td>
                <td valign="top" style="background-color: #e8e8e8">
                    <small>
                    [
                        <a href="/package-changelog.php?package=<?= $this->noHtml($package['name']) ?>&release=<?= $this->noHtml($releaseVersion) ?>">
                            Changelog
                        </a>
                    ]
                    </small>
                </td>
            </tr>

        <?php endforeach ?>
    <?php endif ?>

                </table>
            </td>
        </tr>
    </table>

    <br><br>
<?php endif ?>

<table cellpadding="0" cellspacing="1" style="width: 90%; border: 0px;">
    <tr>
        <td style="background-color: #000000">
            <table cellpadding="2" cellspacing="1" style="width: 100%; border: 0px;">
                <tr style="background-color: #CCCCCC;">
                    <th colspan="2">Dependencies<?= $releaseId ? ' for release '.$version : '' ?></th>
                </tr>

<?php if (empty($downloads) && !$releaseId): ?>
    <tr>
        <td style="background-color: #e8e8e8" colspan="2">
            <i>No releases yet.</i>
        </td>
    </tr>
<?php else: ?>
    <?php
    $rels = $package['releases'];

    // Check if there are too much things to show
    if (count ($rels) > 3) {
        $sliced = true;
        $rels = array_slice($rels, 0, 3);
    } else {
        $sliced = false;
    }

    $rel_trans = [
        'lt' => 'older than %s',
        'le' => 'version %s or older',
        'eq' => 'version %s',
        'ne' => 'any version but %s',
        'gt' => 'newer than %s',
        'ge' => '%s or newer',
    ];

    $dep_type_desc = [
        'pkg'      => 'PEAR Package',
        'pkg_pecl' => 'PECL Package',
        'ext'      => 'PHP Extension',
        'php'      => 'PHP Version',
        'prog'     => 'Program',
        'ldlib'    => 'Development Library',
        'rtlib'    => 'Runtime Library',
        'os'       => 'Operating System',
        'websrv'   => 'Web Server',
        'sapi'     => 'SAPI Backend',
    ];

    // Loop per version
    foreach ($rels as $releaseVersion => $rel):
        $dep_text = '';

        if (!empty($version) && $releaseVersion != $version) {
            continue;
        }

        $deps = isset($package['releases'][$releaseVersion]['deps']) ? $package['releases'][$releaseVersion]['deps'] : [];

        ?>

        <?php if (count($deps) > 0): ?>
            <?php foreach ($deps as $row): ?>
                <?php
                // TODO: We have PEAR installer deps both with the name of PEAR
                // Installer and PEAR, make it consistent here until it is fixed
                // in the db.
                if ($row['name'] == 'PEAR Installer') {
                    $row['name'] = 'PEAR';
                }

                // TODO: Fix up wrong dep types here, until it is fixed in the
                // db, we only have the pecl packages in the db now.
                if ($row['type'] == 'pkg' && $this->findPackage($row['name'])) {
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
                ?>
            <?php endforeach ?>

            <tr>
                <th valign="top" style="background-color: #cccccc"><?= empty($version) ? $this->e('Release '.$releaseVersion.':') : '' ?></th>
                <td valign="top" style="background-color: #e8e8e8"><?= $dep_text ?></td>
            </tr>
        <?php else: ?>
            <tr>
                <th valign="top" style="background-color: #cccccc"><?= empty($version) ? $this->e('Release '.$releaseVersion.':') : '' ?></th>
                <td valign="top" style="background-color: #e8e8e8">No dependencies registered.</td>
            </tr>
        <?php endif ?>
    <?php endforeach ?>

    <?php if ($sliced && '' === $version): ?>
        <tr>
            <td style="background-color: #e8e8e8" colspan="2">
                Dependencies for older releases can be found on the release overview page.
            </td>
        </tr>
    <?php endif ?>
<?php endif ?>
            </table>
        </td>
    </tr>
</table>

<?php if (count($dependants) > 0): ?>
    <br><br>
    <table cellpadding="0" cellspacing="1" style="width: 90%; border: 0px;">
        <tr>
            <td style="background-color: #000000">
                <table cellpadding="2" cellspacing="1" style="width: 100%; border: 0px;">
                    <tr style="background-color: #CCCCCC;">
                        <th>Packages that depend on <?= $this->e($package['name']) ?></th>
                    </tr>
                    <?php foreach ($dependants as $dep): ?>
                        <tr>
                            <td valign="top" style="background-color: #ffffff">
                                <a href="/package/<?= $this->noHtml($dep['p_name']) ?>">
                                    <?= $this->noHtml($dep['p_name']) ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </table>
            </td>
        </tr>
    </table>
<?php endif ?>

<?php $this->end('content') ?>
