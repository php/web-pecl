<?php /* vim: set noet ts=4 sw=4: : */

/**
 * Search for bugs
 *
 * This source file is subject to version 3.0 of the PHP license,
 * that is bundled with this package in the file LICENSE, and is
 * available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.
 * If you did not receive a copy of the PHP license and are unable to
 * obtain it through the world-wide-web, please send a note to
 * license@php.net so we can mail you a copy immediately.
 *
 * @category  peclweb
 * @package   Bugs
 * @copyright Copyright (c) 1997-2004 The PHP Group
 * @license   http://www.php.net/license/3_0.txt  PHP License
 * @version   $Id$
 */

/**
 * Obtain common includes
 */
require_once './include/prepend.inc';


error_reporting(E_ALL ^ E_NOTICE);

if (!empty($_GET['search_for']) &&
    !preg_match('/\\D/', trim($_GET['search_for'])))
{
    if (isset($_COOKIE['PEAR_USER'])) {
        $x = '&edit=1';
    } else {
        if (isset($_COOKIE['MAGIC_COOKIE'])) {
            $x = '&edit=2';
        } else {
            $x = '';
        }
    }
    localRedirect('bug.php?id=' . $_GET['search_for'] . $x);
    exit;
}

response_header('Search');

$errors = array();
$warnings = array();
$order_options = array(
    ''             => 'relevance',
    'id'           => 'ID',
    'ts1'          => 'date',
    'package_name' => 'package',
    'bug_type'     => 'bug_type',
    'status'       => 'status',
    'php_version'  => 'version',
    'php_os'       => 'os',
    'sdesc'        => 'summary',
    'assign'       => 'assignment',
);

define('BOOLEAN_SEARCH', @intval($_GET['boolean']));

if (isset($_GET['cmd']) && $_GET['cmd'] == 'display') {

/*
 * need to move this to DB eventually...
 */
    $mysql4 = version_compare(mysql_get_server_info(), '4.0.0', 'ge');

    if ($mysql4) {
        $query = 'SELECT SQL_CALC_FOUND_ROWS';
    } else {
        $query = 'SELECT';
    }

    $query .= ' bugdb.*, TO_DAYS(NOW())-TO_DAYS(bugdb.ts2) AS unchanged'
            . ' FROM bugdb';

    if (!empty($site) || !empty($_GET['maintain']) || !empty($_GET['handle'])) {
        $query .= ' LEFT JOIN packages ON packages.name = bugdb.package_name';
    }

    if (!empty($_GET['maintain']) || !empty($_GET['handle'])) {
        $query .= ' LEFT JOIN maintains ON packages.id = maintains.package';
        $query .= ' AND maintains.handle = ';
        if (!empty($_GET['maintain'])) {
            $query .= $dbh->quoteSmart($_GET['maintain']);
        } else {
            $query .= $dbh->quoteSmart($_GET['handle']);
        }
    }

    if (empty($_GET['package_name']) || !is_array($_GET['package_name'])) {
        $_GET['package_name'] = array();
        $where_clause = ' WHERE 1=1';
    } else {
        $where_clause = ' WHERE bugdb.package_name';
        if (count($_GET['package_name']) > 1) {
            $where_clause .= " IN ('"
                           . join("', '", escapeSQL($_GET['package_name']))
                           . "')";
        } else {
            $where_clause .= ' = '
                               . $dbh->quoteSmart($_GET['package_name'][0]);
        }
    }

    if (empty($_GET['package_nname']) || !is_array($_GET['package_nname'])) {
        $_GET['package_nname'] = array();
    } else {
        $where_clause .= ' AND bugdb.package_name';
        if (count($_GET['package_nname']) > 1) {
            $where_clause .= " NOT IN ('"
                           . join("', '", escapeSQL($_GET['package_nname']))
                           . "')";
        } else {
            $where_clause .= ' <> '
                           . $dbh->quoteSmart($_GET['package_nname'][0]);
        }
    }

    /*
     * Ensure status is valid and tweak search clause
     * to treat assigned, analyzed, critical and verified bugs as open
     */
    if (empty($_GET['status'])) {
        $status = 'Open';
    } else {
        $status = $_GET['status'];
    }
    switch ($status) {
        case 'Closed':
        case 'Duplicate':
        case 'Critical':
        case 'Assigned':
        case 'Analyzed':
        case 'Verified':
        case 'Suspended':
        case 'Wont fix':
        case 'No Feedback':
        case 'Feedback':
        case 'Bogus':
            $where_clause .= " AND bugdb.status='$status'";
            break;
        case 'Old Feedback':
            $where_clause .= " AND bugdb.status='Feedback'" .
                             ' AND TO_DAYS(NOW())-TO_DAYS(bugdb.ts2) > 60';
            break;
        case 'Fresh':
            $where_clause .= ' AND bugdb.status NOT IN' .
                             " ('Closed', 'Duplicate', 'Bogus')" .
                             ' AND TO_DAYS(NOW())-TO_DAYS(bugdb.ts2) < 30';
            break;
        case 'Stale':
            $where_clause .= ' AND bugdb.status NOT IN' .
                             " ('Closed', 'Duplicate', 'Bogus')" .
                             ' AND TO_DAYS(NOW())-TO_DAYS(bugdb.ts2) > 30';
            break;
        case 'All':
            break;
        case 'Not Assigned':
            $where_clause .= ' AND bugdb.status NOT IN' .
                             " ('Closed', 'Duplicate', 'Bogus', 'Assigned'," .
                             " 'Wont Fix', 'Suspended')";
            break;
        case 'Open':
        default:
            $where_clause .= " AND bugdb.status IN ('Open', 'Assigned'," .
                             " 'Analyzed', 'Critical', 'Verified')";
    }

    if (empty($_GET['search_for'])) {
        $search_for = '';
    } else {
        $search_for = $_GET['search_for'];
        list($sql_search, $ignored) = format_search_string($search_for);
        $where_clause .= $sql_search;
        if (count($ignored) > 0 ) {
            $warnings[] = 'The following words were ignored: ' .
                    rinse(implode(', ', array_unique($ignored)));
        }
    }

    if (empty($_GET['bug_type']) || $_GET['bug_type'] == 'All') {
        $bug_type = '';
    } else {
        $bug_type = $_GET['bug_type'];
        $where_clause .= ' AND bugdb.bug_type = ' . $dbh->quoteSmart($bug_type);
    }

    if (empty($_GET['bug_age']) || !(int)$_GET['bug_age']) {
        $bug_age = 0;
    } else {
        $bug_age = (int)$_GET['bug_age'];
        $where_clause .= ' AND bugdb.ts1 >= '
                       . " DATE_SUB(NOW(), INTERVAL $bug_age DAY)";
    }

    if (empty($_GET['bug_updated']) || !(int)$_GET['bug_updated']) {
        $bug_updated = 0;
    } else {
        $bug_updated = (int)$_GET['bug_updated'];
        $where_clause .= ' AND bugdb.ts2 >= '
                       . " DATE_SUB(NOW(), INTERVAL $bug_updated DAY)";
    }

    if (empty($_GET['php_os'])) {
        $php_os = '';
    } else {
        $php_os = $_GET['php_os'];
        $where_clause .= " AND bugdb.php_os LIKE '%"
                       . $dbh->escapeSimple($php_os) . "%'";
    }

    if (empty($_GET['phpver'])) {
        $phpver = '';
    } else {
        $phpver = $_GET['phpver'];
        $where_clause .= " AND bugdb.php_version LIKE '"
                       . $dbh->escapeSimple($phpver) . "%'";
    }

    if (empty($_GET['handle'])) {
        $handle = '';
        if (empty($_GET['assign'])) {
            $assign = '';
        } else {
            $assign = $_GET['assign'];
            $where_clause .= ' AND bugdb.assign = '
                           . $dbh->quoteSmart($assign);
        }
        if (empty($_GET['maintain'])) {
            $maintain = '';
        } else {
            $maintain = $_GET['maintain'];
            $where_clause .= ' AND maintains.handle = '
                           . $dbh->quoteSmart($maintain);
        }
    } else {
        $handle = $_GET['handle'];
        $where_clause .= ' AND (maintains.handle = '
                       . $dbh->quoteSmart($handle)
                       . ' OR bugdb.assign = '
                       . $dbh->quoteSmart($handle). ')';
    }

    if (empty($_GET['author_email'])) {
        $author_email = '';
    } else {
        $author_email = $_GET['author_email'];
        $where_clause .= ' AND bugdb.email = '
                       . $dbh->quoteSmart($author_email);
    }

    $where_clause .= ' AND (packages.package_type = '
                   . $dbh->quoteSmart($site);

    if ($pseudo = array_intersect($pseudo_pkgs, $_GET['package_name'])) {
        $where_clause .= " OR bugdb.package_name";
        if (count($pseudo) > 1) {
            $where_clause .= " IN ('"
                           . join("', '", escapeSQL($pseudo)) . "')";
        } else {
            $where_clause .= " = '" . implode('', escapeSQL($pseudo)) . "'";
        }
    } else {
        $where_clause .= " OR bugdb.package_name IN ('"
                       . join("', '", escapeSQL($pseudo_pkgs)) . "')";
    }

    $where_clause .= ')';

    $query .= $where_clause;

    if ($_GET['direction'] != 'DESC') {
        $direction = 'ASC';
    } else {
        $direction = 'DESC';
    }

    if (empty($_GET['order_by']) ||
        !array_key_exists($_GET['order_by'], $order_options))
    {
        $order_by = 'id';
    } else {
        $order_by = $_GET['order_by'];
    }

    if (empty($_GET['reorder_by']) ||
        !array_key_exists($_GET['reorder_by'], $order_options))
    {
        $reorder_by = '';
    } else {
        $reorder_by = $_GET['reorder_by'];
        if ($order_by == $reorder_by) {
            $direction = $direction == 'ASC' ? 'DESC' : 'ASC';
        } else {
            $direction = 'ASC';
            $order_by = $reorder_by;
        }
    }

    /* we avoid adding an order by clause if using the full text search */
    if (!strlen($search_for)) {
        $query .= ' ORDER BY ' . $order_by . ' ' . $direction;
        
        // if status Feedback then sort also after last updated time.
        if ($status == 'Feedback') {
            $query .= ', bugdb.ts2 ' . $direction;
        }
    }

    if (empty($_GET['begin']) || !(int)$_GET['begin']) {
        $begin = 0;
    } else {
        $begin = (int)$_GET['begin'];
    }

    if (empty($_GET['limit']) || !(int)$_GET['limit']) {
        if ($_GET['limit'] == 'All') {
            $limit = 'All';
        } else {
            $limit = 30;
            $query .= " LIMIT $begin, $limit";
        }
    } else {
        $limit  = (int)$_GET['limit'];
        $query .= " LIMIT $begin, $limit";
    }

    if (stristr($query, ';')) {
        $errors[] = 'BAD HACKER!! No database cracking for you today!';
    } else {
        $res  =& $dbh->query($query);
        $rows =  $res->numRows();

        if ($mysql4) {
            $total_rows =& $dbh->getOne('SELECT FOUND_ROWS()');
        } else {
            /* lame mysql 3 compatible attempt to allow browsing the search */
            $total_rows = $rows < 10 ? $rows : $begin + $rows + 10;
        }

        if (!$rows) {
            show_bugs_menu($_GET['package_name'][0], $status);
            $errors[] = 'No bugs were found.';
            display_bug_error($errors, 'warnings', '');
        } else {
            $package_name_string = '';
            if (count($_GET['package_name']) > 0) {
                foreach ($_GET['package_name'] as $type_str) {
                    $package_name_string.= '&amp;package_name[]=' . urlencode($type_str);
                }
            }

            $package_nname_string = '';
            if (count($_GET['package_nname']) > 0) {
                foreach ($_GET['package_nname'] as $type_str) {
                    $package_nname_string.= '&amp;package_nname[]=' . urlencode($type_str);
                }
            }

            $link = $_SERVER['PHP_SELF'] .
                    '?cmd=display' .
                    $package_name_string  .
                    $package_nname_string .
                    '&amp;status='      . urlencode(rinse($status)) .
                    '&amp;search_for='  . urlencode(rinse($search_for)) .
                    '&amp;php_os='      . urlencode(rinse($php_os)) .
                    '&amp;boolean='     . BOOLEAN_SEARCH .
                    '&amp;author_email='. urlencode(rinse($author_email)) .
                    '&amp;bug_type='    . $bug_type .
                    '&amp;bug_age='     . $bug_age .
                    '&amp;bug_updated=' . $bug_updated .
                    '&amp;order_by='    . $order_by .
                    '&amp;direction='   . $direction .
                    '&amp;phpver='      . urlencode($phpver) .
                    '&amp;limit='       . $limit .
                    '&amp;handle='      . urlencode($handle) .
                    '&amp;assign='      . urlencode($assign) .
                    '&amp;maintain='    . urlencode($maintain);

            display_bug_error($warnings, 'warnings', 'WARNING:');
            show_bugs_menu($_GET['package_name'], $status);

            ?>

<table border="0" cellspacing="2" width="100%">

<?php show_prev_next($begin, $rows, $total_rows, $link, $limit);?>

 <tr>
  <th class="results"><a href="<?php echo $link;?>&amp;reorder_by=id">ID#</a></th>
  <th class="results"><a href="<?php echo $link;?>&amp;reorder_by=ts1">Date</a></th>
  <th class="results"><a href="<?php echo $link;?>&amp;reorder_by=package_name">Package</a></th>
  <th class="results"><a href="<?php echo $link;?>&amp;reorder_by=bug_type">Type</a></th>
  <th class="results"><a href="<?php echo $link;?>&amp;reorder_by=status">Status</a></th>
  <th class="results"><a href="<?php echo $link;?>&amp;reorder_by=php_version">Version</a></th>
  <th class="results"><a href="<?php echo $link;?>&amp;reorder_by=php_os">OS</a></th>
  <th class="results"><a href="<?php echo $link;?>&amp;reorder_by=sdesc">Summary</a></th>
  <th class="results"><a href="<?php echo $link;?>&amp;reorder_by=assign">Assigned</a></th>
 </tr>
<?php

            while ($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)) {
                echo ' <tr valign="top" class="' . $tla[$row['status']] . '">' . "\n";

                /* Bug ID */
                echo '  <td align="center"><a href="bug.php?id='.$row['id'].'">'.$row['id'].'</a>';
                echo '<br /><a href="bug.php?id='.$row['id'].'&amp;edit=1">(edit)</a></td>' . "\n";

                /* Date */
                echo '  <td align="center">'.date ('Y-m-d H:i:s', strtotime ($row['ts1'])).'</td>' . "\n";
                echo '  <td>', htmlspecialchars($row['package_name']), '</td>' . "\n";
                echo '  <td>', htmlspecialchars(@$types[$row['bug_type']]), '</td>' . "\n";
                echo '  <td>', htmlspecialchars($row['status']);
                if ($row['status'] == 'Feedback' && $row['unchanged'] > 0) {
                    printf ("<br />%d day%s", $row['unchanged'], $row['unchanged'] > 1 ? 's' : '');
                }
                echo '</td>' . "\n";
                echo '  <td>', htmlspecialchars($row['php_version']), '</td>';
                echo '  <td>', $row['php_os'] ? htmlspecialchars($row['php_os']) : '&nbsp;', '</td>' . "\n";
                echo '  <td>', $row['sdesc']  ? clean($row['sdesc'])             : '&nbsp;', '</td>' . "\n";
                echo '  <td>', $row['assign'] ? htmlspecialchars($row['assign']) : '&nbsp;', '</td>' . "\n";
                echo " </tr>\n";
            }

		    show_prev_next($begin, $rows, $total_rows, $link, $limit);
            echo "</table>\n\n";
        }
        
        response_footer();
        exit;
    }
}

display_bug_error($errors);
display_bug_error($warnings, 'warnings', 'WARNING:');

?>
<form id="asearch" method="get" action="<?php echo $_SERVER['PHP_SELF'] ?>">
<table id="primary" width="100%">
<tr valign="top">
  <th>Find bugs</th>
  <td style="white-space: nowrap">with all or any of the w<span class="accesskey">o</span>rds</td>
  <td style="white-space: nowrap"><input type="text" name="search_for" value="<?php echo clean($search_for);?>" size="20" maxlength="255" accesskey="o" />
      <br /><small><?php show_boolean_options(BOOLEAN_SEARCH) ?>
      (<?php print_link('http://bugs.php.net/search-howto.php', '?', true);?>)</small>
  </td>
  <td rowspan="3">
   <select name="limit"><?php show_limit_options($limit);?></select>
   &nbsp;
   <select name="order_by"><?php show_order_options($limit);?></select>
   <br />
   <small>
    <input type="radio" name="direction" value="ASC" <?php if($direction != "DESC") { echo('checked="checked"'); }?>/>Ascending
    &nbsp;
    <input type="radio" name="direction" value="DESC" <?php if($direction == "DESC") { echo('checked="checked"'); }?>/>Descending
   </small>
   <br /><br />
   <input type="hidden" name="cmd" value="display" />
   <label for="submit" accesskey="r">Sea<span class="accesskey">r</span>ch:</label>
   <input id="submit" type="submit" value="Search" />
  </td>
</tr>
<tr valign="top">
  <th>Status</th>
  <td style="white-space: nowrap">
   <label for="status" accesskey="n">Retur<span class="accesskey">n</span> bugs
   with <b>status</b></label>
  </td>
  <td><select id="status" name="status"><?php show_state_options($status);?></select></td>
</tr>
<tr valign="top">
  <th>Type</th>
  <td style="white-space: nowrap">
   <label for="bug_type">Return bugs with <b>type</b></label>
  </td>
  <td><select id="bug_type" name="bug_type"><?php show_type_options($bug_type, true);?></select></td>
</tr>
</table>

<table style="font-size: 100%;">
<tr valign="top">
  <th><label for="category" accesskey="c">Pa<span class="accesskey">c</span>kge</label></th>
  <td style="white-space: nowrap">Return bugs for these <b>packages</b></td>
  <td><select id="category" name="package_name[]" multiple="multiple" size="6"><?php show_types($package_name,2);?></select></td>
</tr>
<tr valign="top">
  <th>&nbsp;</th>
  <td style="white-space: nowrap">Return bugs <b>NOT</b> for these <b>packages</b></td>
  <td><select name="package_nname[]" multiple="multiple" size="6"><?php show_types($package_nname,2);?></select></td>
</tr>
<tr valign="top">
  <th>OS</th>
  <td style="white-space: nowrap">Return bugs with <b>operating system</b></td>
  <td><input type="text" name="php_os" value="<?php echo clean($php_os);?>" /></td>
</tr>
<tr valign="top">
  <th>Version</th>
  <td style="white-space: nowrap">Return bugs reported with <b>PHP version</b></td>
  <td><input type="text" name="phpver" value="<?php echo clean($phpver);?>" /></td>
</tr>
<tr valign="top">
  <th>Assigned</th>
  <td style="white-space: nowrap">Return bugs <b>assigned</b> to</td>
  <td><input type="text" name="assign" value="<?php echo clean($assign);?>" />
<?php
    if (!empty($_COOKIE['PEAR_USER'])) {
        $u = rinse($_REQUEST['PEAR_USER']);
        print "<input type=\"button\" value=\"set to $u\" onclick=\"form.assign.value='$u'\" />";
    }
?>
  </td>
</tr>
  <tr valign="top">
  <th>Maintainer</th>
  <td nowrap="nowrap">Return only bugs in packages <b>maintained</b> by</td>
  <td><input type="text" name="maintain" value="<?php echo clean($maintain);?>" />
<?php
    if (!empty($_COOKIE['PEAR_USER'])) {
        $u = stripslashes($_REQUEST['PEAR_USER']);
        print "<input type=\"button\" value=\"set to $u\" onclick=\"form.maintain.value='$u'\" />";
    }
?>
  </td>
 </tr>
<tr valign="top">
  <th>Author e<span class="accesskey">m</span>ail</th>
  <td style="white-space: nowrap">Return bugs with author email</td>
  <td><input accesskey="m" type="text" name="author_email" value="<?php echo clean($author_email); ?>" /></td>
</tr>
<tr valign="top">
  <th>Date</th>
  <td style="white-space: nowrap">Return bugs submitted</td>
  <td><select name="bug_age"><?php show_byage_options($bug_age);?></select></td>
 </tr>
 <tr>
  <td>&nbsp;</td><td style="white-space: nowrap">Return bugs updated</td>
  <td><select name="bug_updated"><?php show_byage_options($bug_updated);?></select></td>
</tr>
</table>
</form>


<?php
response_footer();

function show_prev_next($begin, $rows, $total_rows, $link, $limit)
{
    echo "<!-- BEGIN PREV/NEXT -->\n";
    echo " <tr>\n";
    echo '  <td class="search-prev_next" colspan="9">' . "\n";

    if ($limit=='All') {
        echo "$total_rows Bugs</td></tr>\n";
        return;
    }

    echo '   <table border="0" cellspacing="0" cellpadding="0" width="100%">' . "\n";
    echo "    <tr>\n";
    echo '     <td class="class-prev">';
    if ($begin > 0) {
        echo '<a href="' . $link . '&amp;begin=';
        echo max(0, $begin - $limit);
        echo '">&laquo; Show Previous ' . $limit . ' Entries</a>';
    } else {
        echo '&nbsp;';
    }
    echo "</td>\n";

    echo '     <td class="search-showing">Showing ' . ($begin+1);
    echo '-' . ($begin+$rows) . ' of ' . $total_rows . "</td>\n";

    echo '     <td class="search-next">';
    if ($begin+$rows < $total_rows) {
        echo '<a href="' . $link . '&amp;begin=' . ($begin+$limit);
        echo '">Show Next ' . $limit . ' Entries &raquo;</a>';
    } else {
        echo '&nbsp;';
    }
    echo "</td>\n    </tr>\n   </table>\n  </td>\n </tr>\n";
    echo "<!-- END PREV/NEXT -->\n";
}

function show_order_options($current)
{
    global $order_options;
    foreach ($order_options as $k => $v) {
        echo '<option value="', $k, '"',
             ($v == $current ? ' selected="selected"' : ''),
             '>Sort by ', $v, "</option>\n";
    }
}
