<?php $this->extend('layout.php', ['title' => 'Package Statistics']) ?>

<?php $this->start('content') ?>

<h1>Package Statistics</h1>

<table cellpadding="0" cellspacing="1" style="width: 90%; border: 0px;">
    <tr>
        <td bgcolor="#000000">
            <table cellpadding="2" cellspacing="1" style="width: 100%; border: 0px;">
                <tr style="background-color: #CCCCCC;"><th>Select Package</th></tr>
                <tr bgcolor="#ffffff">
                    <td>

                        <form action="/package-stats.php" method="get">
                            <table>
                                <tr>
                                    <td>
                                        <select name="cid" onchange="javascript:reloadMe();">
                                            <option value="">Select category...</option>

                                            <?php foreach ($categories as $category): ?>
                                                <?php $selected = (isset($_GET['cid']) && $_GET['cid'] == $category['id']) ? 'selected' : '' ?>

                                                <option value="<?= $this->e($category['id']) ?>" <?= $selected ?>>
                                                    <?= $this->e($category['name']) ?>
                                                </option>
                                            <?php endforeach ?>
                                        </select>
                                    </td>
                                    <td>

                                    <?php if (!empty($_GET['cid'])): ?>
                                        <select name="pid" onchange="javascript:reloadMe();">
                                            <option value="">Select package ...</option>

                                            <?php foreach ($packages as $id => $name): ?>
                                                <?php $selected = (isset($_GET['pid']) && $_GET['pid'] == $id) ? 'selected' : '' ?>

                                                <option value="<?= $this->e($id) ?>" <?= $selected ?>>
                                                    <?= $this->e($name) ?>
                                                </option>
                                            <?php endforeach ?>

                                        </select>
                                    <?php else: ?>
                                        <input type="hidden" name="pid" value="">
                                    <?php endif ?>

                                    </td>
                                    <td>

                                    <?php if (!empty($_GET['pid'])): ?>
                                        <select onchange="javascript:reloadMe();" name="rid" size="1">
                                            <option value="">All releases</option>

                                            <?php foreach ($releases as $release): ?>
                                                <?php $selected = (isset($_GET['rid']) && $_GET['rid'] == $release['id']) ? 'selected' : '' ?>

                                                <option value="<?= $this->e($release['id']) ?>" <?= $selected ?>>
                                                    <?= $this->e($release['version']) ?>
                                                </option>
                                            <?php endforeach ?>
                                        </select>
                                    <?php else: ?>
                                        <input type="hidden" name="rid" value="">
                                    <?php endif ?>

                                    </td>
                                </tr>
                                <tr>
                                    <td><input type="submit" name="submit" value="Go" /></td>
                                </tr>
                            </table>
                        </form>

                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<?php if (!empty($_GET['pid'])): ?>

    <table cellpadding="0" cellspacing="1" style="width: 90%; border: 0px;">
        <tr>
            <td bgcolor="#000000">
                <table cellpadding="2" cellspacing="1" style="width: 100%; border: 0px;">
                    <tr style="background-color: #CCCCCC;">
                        <th>General Statistics</th>
                    </tr>
                    <tr bgcolor="#ffffff">
                        <td>

                            <?php if (isset($info['releases']) && count($info['releases'])>0): ?>
                                <h2>&raquo; Statistics for Package &quot;<a href="/package/<?= $this->e($info['name']) ?>"><?= $this->e($info['name']) ?></a>&quot;</h2>
                                Number of releases: <strong><?= count($info['releases']) ?></strong><br>
                                Total downloads: <strong><?= number_format($packageStatsRepository->getDownloadsByPackageId($_GET['pid']), 0, '.', ',') ?></strong><br>
                            <?php else: ?>
                                No package or release found.
                            <?php endif ?>

                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <?php if (count($info['releases']) > 0): ?>
        <br>
        <table cellpadding="0" cellspacing="1" style="width: 90%; border: 0px;">
            <tr>
                <td bgcolor="#000000">
                    <table cellpadding="2" cellspacing="1" style="width: 100%; border: 0px;">
                        <tr style="background-color: #CCCCCC;">
                            <th>Release Statistics</th>
                        </tr>
                        <tr bgcolor="#ffffff">
                            <td>
                                <table cellspacing="0" cellpadding="3" style="border: 0px; width: 100%;">
                                    <tr>
                                        <th style="text-align: left;">Version</th>
                                        <th style="text-align: left;">Downloads</th>
                                        <th style="text-align: left;">Released</th>
                                        <th style="text-align: left;">Last Download</th>
                                    </tr>
                                    <?php
                                        $releasesStats = $packageStatsRepository->getReleasesStats(
                                            $_GET['pid'],
                                            (isset($_GET['rid']) ? $_GET['rid'] : null)
                                        );
                                    ?>

                                    <?php foreach ($releasesStats as $value): ?>
                                        <tr>
                                            <td>
                                                <a href="/package/<?= $this->e($info['name']) ?>/<?= $this->e($value['release']) ?>">
                                                    <?= $this->e($value['release']) ?>
                                                </a>
                                            </td>
                                            <td><?= number_format($value['dl_number'], 0, '.', ',') ?></td>
                                            <td><?= $this->formatDateToUtc($value['releasedate'], 'Y-m-d'); ?>
                                            </td>
                                            <td><?= $this->formatDateToUtc($value['last_dl']); ?></td>
                                        </tr>
                                    <?php endforeach ?>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <br>
        <img src="package-stats-graph.php?pid=<?= $this->e($_GET['pid']) ?>&releases=<?= isset($_GET['rid']) ? $this->e($_GET['rid']) : '' ?>_339900" name="stats_graph" width="543" height="200" alt="" />
        <br>
        <br>

        <form name="graph_control" action="#">
            <input type="hidden" name="pid" value="<?= isset($_GET['pid']) ? $this->e($_GET['pid']) : ''; ?>">
            <input type="hidden" name="rid" value="<?= isset($_GET['rid']) ? $this->e($_GET['rid']) : ''; ?>">
            <input type="hidden" name="cid" value="<?= isset($_GET['cid']) ? $this->e($_GET['cid']) : ''; ?>">
            <table border="0">
                <tr>
                    <td colspan="2">
                        Show graph of:<br>
                        <select style="width: 543px" name="graph_list" size="5"></select>
                    </td>
                </tr>
                <tr>
                    <td valign="top">
                        Release:
                        <select name="releases">
                            <option value="">Select...</option>
                            <option value="0">All</option>
                            <?php foreach($releases as $release): ?>
                                <option value="<?= $this->e($release['id']) ?>"><?= $this->e($release['version']) ?></option>
                            <?php endforeach ?>
                        </select>
                        Colour:
                        <select name="colours">
                            <option>Select...</option>
                            <option value="339900">Green</option>
                            <option value="dd0000">Red</option>
                            <option value="003399">Blue</option>
                            <option value="000000">Black</option>
                            <option value="999900">Yellow</option>
                        </select>
                    </td>
                    <td align="right">
                        <input type="submit" style="width: 100px" name="add" value="Add" onclick="addGraphItem(); return false;">
                        <input type="submit" style="width: 100px" name="remove" value="Remove" onclick="removeGraphItem(); return false">
                    </td>
                </tr>
                <tr>
                    <td align="center" colspan="2">
                        <input type="submit" name="update" value="Update graph" onclick="updateGraph(); return false">
                    </td>
                </tr>
            </table>
        </form>
        <br>
    <?php endif ?>
<?php endif ?>

<?php if (empty($_GET['pid'])): ?>
    <br>
    <table cellpadding="0" cellspacing="1" style="width: 90%; border: 0px;">
    <tr>
        <td bgcolor="#000000">
            <table cellpadding="2" cellspacing="1" style="width: 100%; border: 0px;">
                <tr style="background-color: #CCCCCC;">
                    <th>
                        <?php if (!empty($_GET['cid'])): ?>
                            Category Statistics for:
                            <i><a href="packages.php?catpid=<?= $this->e($_GET['cid']) ?>&amp;catname=<?= $this->e(str_replace(' ', '+', $categoryName)) ?>"><?= $this->e($categoryName) ?></a></i>
                        <?php else: ?>
                            Global Statistics
                        <?php endif ?>
                    </th>
                </tr>
                <tr bgcolor="#ffffff">
                    <td>
                        <table border="0" width="100%">
                            <tr>
                                <td style="width: 25%;">Total&nbsp;Packages:</td>
                                <td align="center" style="width: 25%; background-color: #CCCCCC;"><?= $this->e($totalPackages) ?></td>
                                <td style="width: 25%;">Total&nbsp;Releases:</td>
                                <td align="center" style="width: 25%; background-color: #CCCCCC;"><?= $this->e($totalReleases) ?></td>
                            </tr>
                            <tr>
                                <td style="width: 25%;">Total&nbsp;Maintainers:</td>
                                <td align="center" style="width: 25%; background-color: #CCCCCC;"><?= $this->e($totalMaintainers) ?></td>
                                <td style="width: 25%;">Total&nbsp;Categories:</td>
                                <td align="center" style="width: 25%; background-color: #CCCCCC;"><?= $this->e($totalCategories) ?></td>
                            </tr>
                            <?php if(empty($_GET['cid'])): ?>
                                <tr>
                                    <td width="25%">Total&nbsp;Downloads:</td>
                                    <td width="25%" align="center" bgcolor="#cccccc"><?= $this->e($totalDownloads) ?></td>
                                </tr>
                            <?php endif ?>
                        </table>

                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<br>

<table cellpadding="0" cellspacing="1" style="width: 90%; border: 0px;">
    <tr>
        <td bgcolor="#000000">
            <table cellpadding="2" cellspacing="1" style="width: 100%; border: 0px;">
                <tr style="background-color: #CCCCCC;">
                    <th>Package Statistics</th>
                </tr>
                <tr bgcolor="#ffffff">
                    <td>

                        <table border="0" width="100%" cellpadding="2" cellspacing="2">
                            <tr align="left" bgcolor="#cccccc">
                                <th>Package Name</th>
                                <th><span class="accesskey"># of downloads</span></th>
                                <th>&nbsp;</th>
                            </tr>

                            <?php foreach ($results as $row): ?>
                                <tr bgcolor="#eeeeee">
                                <td><a href="/package/<?= $this->e($row['package']) ?>"><?= $this->e($row['package']) ?></a></td>
                                <td><?= number_format($row['dl_number'], 0, '.', ',') ?></td>
                                <td>[<a href="/package-stats.php?cid=<?= $this->e($row['cid']) ?>'&amp;pid=<?= $this->e($row['pid']) ?>">Details</a>]</td>
                                </tr>
                            <?php endforeach ?>
                        </table>

                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<?php endif ?>

<script>
    function clearGraphList()
    {
        graphForm = document.forms['graph_control'];
        for (i=0; i<graphForm.graph_list.options.length; i++) {
            graphForm.graph_list.options[i] = null;
        }
    }

    function addGraphItem()
    {
        graphForm = document.forms['graph_control'];
        selectedRelease = graphForm.releases.options[graphForm.releases.selectedIndex];
        selectedColour  = graphForm.colours.options[graphForm.colours.selectedIndex];

        if (selectedRelease.value != "" && selectedColour.value != "" && selectedRelease.value != "Select..." && selectedColor.value != "Select...") {
            newText  = 'Release ' + selectedRelease.text + ' in ' + selectedColour.text;
            newValue = selectedRelease.value + '_' + selectedColour.value;
            graphForm.graph_list.options[graphForm.graph_list.options.length] = new Option(newText, newValue);
        } else {
            alert('Please select a release and a colour!');
        }
    }

    function removeGraphItem()
    {
        graphForm = document.forms['graph_control'];
        graphList = graphForm.graph_list;

        if (graphList.selectedIndex != null) {
            graphList.options[graphList.selectedIndex] = null;
        }
    }

    function updateGraph()
    {
        graphForm   = document.forms['graph_control'];
        releases_qs = '';

        if (graphForm.graph_list.options.length) {
            for (i=0; i<graphForm.graph_list.options.length; i++) {
                if (i == 0) {
                    releases_qs += graphForm.graph_list.options[i].value;
                } else {
                    releases_qs += ',' + graphForm.graph_list.options[i].value;
                }
            }
            graphForm.update.value = 'Updating...';
            document.images['stats_graph'].src = 'package-stats-graph.php?pid=<?= $this->noHtml($_GET['pid']) ?>&releases=' + releases_qs;
            graphForm.update.value = 'Update graph';
        } else {
            alert('Please select one or more releases to show!');
        }
    }

    function reloadMe()
    {
        var newLocation = '<?= $this->e($_SERVER['PHP_SELF']) ?>?'
                        + 'cid='
                        + document.forms[1].cid.value
                        + '&pid='
                        + document.forms[1].pid.value
                        + '&rid='
                        + document.forms[1].rid.value;

        document.location.href = newLocation;
    }
</script>

<?php $this->end('content') ?>
