<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001 The PHP Group                                     |
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
   +----------------------------------------------------------------------+
   $Id$
 */

auth_require();
require_once "HTML/Form.php";

response_header("Package statistics");
?>

<h1>Package statistics</h1>

<script language="JavaScript">
<!--
function reloadMe()
{
    var newLocation = '<?php echo $_SERVER['PHP_SELF']; ?>?'
                      + 'cid='
                      + document.forms[1].cid.value
                      + '&pid='
                      + document.forms[1].pid.value
                      + '&rid='
                      + document.forms[1].rid.value;

    document.location.href = newLocation;
                             
}
//-->
</script>

<?php
/** Get packages for the user */
if (User::isAdmin($_COOKIE['PEAR_USER'])) {
    $query = "SELECT * FROM packages"
             . ((isset($_GET['cid']) && $_GET['cid'] != "") ? " WHERE category = '" . $_GET['cid'] . "'" : "")
             . " ORDER BY name";
} else {
    $query = "SELECT p.* FROM packages p, mantains m WHERE p.id = m.package"
             . " AND m.handle = '" . $_COOKIE['PEAR_USER'] . "'"
             . ((isset($_GET['cid']) && $_GET['cid'] != "") ? " AND category = '" . $_GET['cid'] . "'" : "")
             . " ORDER BY p.name";
}

$sth = $dbh->query($query);

while ($row = $sth->fetchRow(DB_FETCHMODE_ASSOC)) {
    $packages[$row['id']] = $row['name'];
}

$bb = new Borderbox("Select package");

// Don't use HTML_Form here since we need to use some custom javascript here
echo "<form action=\"package-stats.php\" method=\"get\">\n";
echo "<table>\n";
echo "<tr>\n";
echo "  <td>\n";
echo "  <select name=\"cid\" onchange=\"javascript:reloadMe();\">\n";
echo "    <option>Select category ...</option>\n";

foreach (category::listAll() as $value) {
    if (isset($_GET['cid']) && $_GET['cid'] == $value['id']) {
        echo "    <option value=\"" . $value['id'] . "\" selected>" . $value['name'] . "</option>\n";
    } else {
        echo "    <option value=\"" . $value['id'] . "\">" . $value['name'] . "</option>\n";
    }    
}

echo "  </select>\n";
echo "  </td>\n";
echo "  <td>\n";

if (isset($_GET['cid']) && $_GET['cid'] != "") {
    echo "  <select name=\"pid\" onchange=\"javascript:reloadMe();\">\n";
    echo "    <option>Select package ...</option>\n";

    foreach ($packages as $value => $name) {
        if (isset($_GET['pid']) && $_GET['pid'] == $value) {
            echo "    <option value=\"" . $value . "\" selected>" . $name . "</option>\n";
        } else {
            echo "    <option value=\"" . $value . "\">" . $name . "</option>\n";
        }    
    }

    echo "</select>\n";
} else {
    echo "<input type=\"hidden\" name=\"pid\" value=\"\" />\n";
}

echo "  </td>\n";
echo "  <td>\n";

if (isset($_GET['pid']) && $_GET['pid'] != "") {
    echo "  <select onchange=\"javascript:reloadMe();\" name=\"rid\" size=\"1\">\n";
    echo "  <option>Select release ...</option>\n";
    echo "  <option>All releases</option>\n";

    $query = "SELECT id, version FROM releases WHERE package = '" . $_GET['pid'] . "'";
    $rows = $dbh->getAll($query, DB_FETCHMODE_ASSOC);

    foreach ($rows as $row) {
        if (isset($_GET['rid']) && $_GET['rid'] == $row['id']) {
            echo "    <option value=\"" . $row['id'] . "\" selected>" . $row['version'] . "</option>\n";        
        } else {
            echo "    <option value=\"" . $row['id'] . "\">" . $row['version'] . "</option>\n";
        }
    }

    echo "  </select>\n";
} else {
    echo "<input type=\"hidden\" name=\"rid\" value=\"\" />\n";
}

echo "  </td>\n";

echo "</tr>\n";
echo "<tr>\n";
echo "  <td><input type=\"submit\" name=\"submit\" value=\"Go\"></td>\n";
echo "</tr>\n";
echo "</table>\n";
echo "</form>\n";

$bb->end();

if (isset($_GET['pid']) && $_GET['pid'] != "") {

    if (User::isAdmin($_COOKIE['PEAR_USER']) || 
        maintainer::get($_GET['pid']) == $_COOKIE['PEAR_USER'])
    {
        $info = package::info($_GET['pid']);
        
        echo "<h2>Statistics for package \"" . $info['name'] . "\"</h2>\n";

        $bb = new Borderbox("General statistics");
        echo "Number of releases: <b>" . count($info['releases']) . "</b><br />\n";
        echo "Total downloads: <b>" . statistics::package($_GET['pid']) . "</b><br />\n";
        $bb->end();

        if (count($info['releases']) > 0) {
            echo "<br />\n";
            $bb = new Borderbox("Release statistics");

            $release_statistics = statistics::release($_GET['pid'], (isset($_GET['rid']) ? $_GET['rid'] : ""));

            $i= 0;
            foreach ($release_statistics as $key => $value) {
                $bb2 = new Borderbox("Release: " . $value['version'], 400);
                echo "Number of downloads: <b>" . $value['total'] . "</b><br />\n";

                if ($value['total'] > 1) {
                    echo "First download: <b>" . $value['first_download'] . "</b><br />\n";
                    echo "Last download: <b>" . $value['last_download'] . "</b><br />\n";
                }

                $bb2->end();
                echo "<br />\n";
            }
            
            $bb->end();
        }

    } else {
        PEAR::raiseError("Not enough priviliges to view statistics for"
                         . " this package.");
    }
}

response_footer();
?>
