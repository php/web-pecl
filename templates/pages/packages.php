<?php $this->extend('layout.php', ['title' => $title]) ?>

<?php $this->start('content') ?>

<script>
    function toggleMoreInfo(packageName, packageID)
    {
        if (!document.getElementById) {
            location.href = '/package/' + packageName;
            return;
        }

        var rowLayer = document.getElementById('moreInfo_' + packageID);
        var rowSpacerLayer = document.getElementById('moreInfo_' + packageID + '_spacer');

        newDisplay = rowLayer.style.display == 'none' ? 'inline' : 'none';
        rowLayer.style.display       = newDisplay;
        rowSpacerLayer.style.display = newDisplay;
    }
</script>

<table border="0" width="100%">
    <tr>
        <th valign="top" align="left">
            Contents of :: <?= $breadcrumbs ?>
        </th>
        <td valign="top" align="right">
            <?= $showEmptyLink ?>
        </td>
    </tr>
</table>

<?php if (count($categories) > 0): ?>
    <table border="0" cellpadding="6" cellspacing="2" width="100%">
        <?php for ($i = 0; $i < ceil(count($categories)/2); $i++): ?>
            <tr>
                <td width="50%">
                    <?php if (!empty($categories[2 * $i])): ?>
                        <?= $categories[2 * $i] ?>
                    <?php endif ?>
                </td>
                <td width="50%">
                    <?php if (!empty($categories[2 * $i + 1])): ?>
                        <?= $categories[2 * $i + 1] ?>
                    <?php endif ?>
                </td>
            </tr>
        <?php endfor ?>
    </table>
<?php endif ?>

<?php if($catpid && $packages): ?>

    <br>

    <?php if (isset($subCategories)): ?>
        Sub-categories: <?= $subCategories ?><br><br>
    <?php endif ?>

    <a href="<?= $hideMoreInfoLink ?>" title="Hide all more info">
        <img src="/img/moreinfo-no.gif" width="17" height="17" border="0" vspace="3" />
    </a>
    <a href="<?= $showMoreInfoLink ?>" title="Show all more info">
        <img src="/img/moreinfo-yes.gif" width="17" height="17" border="0" vspace="3" />
    </a>

    <table border="0" style="border: solid 1px black" cellspacing="0" cellpadding="0">
        <tr>
            <td>
                <table border="0" width="100%" cellspacing="0">
                    <tr class="tableHeader">
                        <td class="tableHeader" width="50" align="left">&nbsp;<?= $prev ?></td>
                        <td class="tableHeader" align="center">Packages (<?= $from ?> - <?= $to ?> of <?= $total ?>)</td>
                        <td class="tableHeader" width="50" align="right"><?= $next ?>&nbsp;</td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td>
                <table border="0" id="packageList">
                    <tr>
                        <th class="pkgListHeader">#</td>
                        <th class="pkgListHeader">Package name</td>
                        <th class="pkgListHeader">Description</td>
                        <td>&nbsp;</td>
                    </tr>

                    <?php foreach($packages as $p): ?>
                        <tr>
                            <td valign="top"><?= $from++ ?></td>
                            <td valign="top"><a href="/package/<?= $this->e($p['name']) ?>"><strong><?= $this->e($p['name']) ?></strong></td>
                            <td valign="top"><?= $this->e($p['summary']) ?></td>
                            <td valign="top">
                                <a href="#" onclick="toggleMoreInfo('<?= $this->e($p['name']) ?>',<?= $this->e($p['id']) ?>); return false" onmouseover="window.status = 'View more info about <?= $this->e($p['name']) ?>'; return true" onmouseout="window.status = ''" title="View more info about <?= $this->e($p['name']) ?>">
                                    <img src="/img/moreinfo.gif" border="0" />
                                </a>
                            </td>
                        </tr>

                        <tr <?= ('none' === $defaultMoreInfoVis) ? 'style="display:'.$defaultMoreInfoVis.'"' : '' ?> id="moreInfo_<?= $p['id'] ?>">
                            <td>&nbsp;</td>

                            <td colspan="3" class="moreInfo">
                                <table border="0" class="moreInfoHeader" width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td><span class="moreInfoText">More information</span></td>
                                        <td align="right">
                                            <a href="javascript: toggleMoreInfo('<?= $this->e($p['name']) ?>',<?= $this->e($p['id']) ?>)"><img class="closeButton" src="/img/close.gif" border="0" /></a>
                                        </td>
                                    </tr>
                                </table>

                                <table border="0">
                                    <tr>
                                        <td class="eInfo_label"><strong>Number of releases:</strong></td>
                                        <td><?= $this->e($p['eInfo']['numReleases']) ?></td>

                                        <td class="eInfo_label"><strong>License type:</strong></td>
                                        <td><?= $this->e($p['eInfo']['license']) ?></td>
                                    </tr>

                                    <tr>
                                        <td class="eInfo_label"><strong>Status:</strong></td>
                                        <td><?= $p['eInfo']['status'] ?></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>

                        <tr <?= 'none' === $defaultMoreInfoVis ? 'style="display:'.$defaultMoreInfoVis.'"' : '' ?> id="moreInfo_<?= $this->noHtml($p['id']) ?>_spacer">
                            <td colspan="5">&nbsp;</td>
                        </tr>
                    <?php endforeach ?>
                </table>
            </td>
        </tr>
    </table>

<?php elseif ($catpid): ?>
    <p align="center">No packages found in this category</p>
<?php endif ?>

<br>

<p align="center">
    <?php if(!$catpid): ?>
        Total number of packages: <?= $this->e($totalPackages) ?><br>
        <a href="/package-stats.php">View package statistics</a>
    <?php else: ?>
        <a href="/package-stats.php?cid=<?= $this->e($catpid) ?>">Statistics for category "<?= $this->e($catname) ?>"</a>
    <?php endif ?>
</p>

<?php $this->end('content') ?>
