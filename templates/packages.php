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
  | Authors:                                                             |
  +----------------------------------------------------------------------+
*/

?>

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
            Contents of :: <?php html_category_urhere($catpid, false); ?>
        </th>
        <td valign="top" align="right">
            <?php echo $showempty_link; ?>
        </td>
    </tr>
</table>

<?php if($nrow) { ?>
    <?php echo $table->toHtml(); ?>
<?php } ?>

<?php if($catpid and $packages) { ?>

    <br />

    <?php if ($subCategories) { ?>
        Sub-categories: <?php echo $subCategories; ?><br /><br />
    <?php } ?>

    <a href="<?php echo $hideMoreInfoLink; ?>" title="Hide all more info"><img src="gifs/moreinfo-no.gif" width="17" height="17" border="0" vspace="3" /></a>
    <a href="<?php echo $showMoreInfoLink; ?>" title="Show all more info"><img src="gifs/moreinfo-yes.gif" width="17" height="17" border="0" vspace="3" /></a>

    <table border="0" style="border: solid 1px black" cellspacing="0" cellpadding="0">
        <tr>
            <td>
                <table border="0" width="100%" cellspacing="0">
                    <tr class="tableHeader">
                        <td class="tableHeader" width="50" align="left">&nbsp;<?php echo $prev; ?></td>
                        <td class="tableHeader" align="center">Packages (<?php echo $first; ?> - <?php echo $last; ?> of <?php echo $total; ?>)</td>
                        <td class="tableHeader" width="50" align="right"><?php echo $next; ?>&nbsp;</td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr>
            <td>
                <table border="0" id="packageList">
                    <tr>
                        <th class="pkgListHeader">#</td>
                        <th class="pkgListHeader"><nobr>Package name</nobr></td>
                        <th class="pkgListHeader">Description</td>
                        <td>&nbsp;</td>
                    </tr>

                    <?php foreach($packages as $p) { ?>
                        <tr>
                            <td valign="top"><?php echo ($first++); ?></td>
                            <td valign="top"><a href="/package/<?php echo $p['name']; ?>"><strong><?php echo $p['name']; ?></strong></td>
                            <td valign="top"><?php echo $p['summary']; ?></td>
                            <td valign="top">
                                <a href="#" onclick="toggleMoreInfo(<?php echo "'".$p['name']."'"; ?>,<?php echo $p['id']; ?>); return false" onmouseover="window.status = 'View more info about <?php echo $p['name']; ?>'; return true" onmouseout="window.status = ''" title="View more info about <?php echo $p['name']; ?>">
                                    <img src="gifs/moreinfo.gif" border="0" />
                                </a>
                            </td>
                        </tr>

                        <tr <?php if($defaultMoreInfoVis == 'none') { ?> style="display: <?php echo $defaultMoreInfoVis; ?>" <?php } ?> id="moreInfo_<?php echo $p['id']; ?>">
                            <td>&nbsp;</td>

                            <td colspan="3" class="moreInfo">
                                <table border="0" class="moreInfoHeader" width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td><span class="moreInfoText">More information</span></td>
                                        <td align="right">
                                            <a href="javascript: toggleMoreInfo(<?php echo "'".$p['name']."'"; ?>,<?php echo $p['id']; ?>)"><img class="closeButton" src="gifs/close.gif" border="0" /></a>
                                        </td>
                                    </tr>
                                </table>

                                <table border="0">
                                    <tr>
                                        <td class="eInfo_label"><strong>Number of releases:</strong></td>
                                        <td><?php echo $p['eInfo']['numReleases']; ?></td>

                                        <td class="eInfo_label"><strong>License type:</strong></td>
                                        <td><?php echo $p['eInfo']['license']; ?></td>
                                    </tr>

                                    <tr>
                                        <td class="eInfo_label"><strong>Status:</strong></td>
                                        <td><?php echo $p['eInfo']['status']; ?></td>
                                    </tr>
                                </table>


                            </td>
                        </tr>

                        <tr <?php if($defaultMoreInfoVis == 'none') { ?> style="display: <?php echo $defaultMoreInfoVis; ?>" <?php } ?> id="moreInfo_<?php echo $p['id']; ?>_spacer"><td colspan="5">&nbsp;</td></tr>
                    <?php } ?>
                </table>
            </td>
        </tr>
    </table>

<?php } elseif ($catpid) { ?>
    <p align="center">
        No packages found in this category
    </p>
<?php } ?>

<br />

<p align="center">
    <?php if(!$catpid) {?>
        Total number of packages: <?php echo $totalpackages; ?><br />
        <?php make_link('/package-stats.php', 'View package statistics'); ?>
    <?php } else { ?>
        <a href="/package-stats.php?cid=<?php echo $catpid; ?>">Statistics for category "<?php echo $catname; ?>"</a>
    <?php } ?>
</p>

<?php response_footer();
