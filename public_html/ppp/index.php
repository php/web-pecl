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
   +----------------------------------------------------------------------+
   $Id$
*/

auth_require(true);
require_once "ppp/pear-ppp.php";

$id = (int)basename($_SERVER['PHP_SELF']);
if ($id != 0) {
    localRedirect("/ppp/info.php/" . $id);
}

response_header('PEAR Package Proposals');
?>

<h2>PEAR Package Proposals</h2>

<h3>Introduction</h3>

<p>Welcome to the <b>PEAR Package Proposals</b> system. Via this website,
the PEAR project manages all new projects, which should be added to
the repository.</p>

<h3>Current proposals</h3>

<p>This is the list of the package prosals that are currently
open. If you like one of the projects mentioned in the list, feel
free give it a positive vote. If you do not like a package for
some reason, give it a negative vote.</p>

<?php
$bb = new BorderBox("Currently open");

$list = proposal::listAll();
if (count($list) == 0) {
   echo "<i>Currently there are no open proposals.</i>";
} else {
?>

<table width="100%" border="0" cellspacing="1" cellpadding="5">
<tr bgcolor="#cccccc">
  <th rowspan="2">Name</th>
  <th rowspan="2">Category</th>
  <th rowspan="2">Summary</th>
  <th rowspan="2">Voting ends in</th>
  <th colspan="2">Votes</th>
  <th rowspan="2">Actions</th>
</tr>
<tr bgcolor="#cccccc">
  <th>pos.</th>
  <th>neg.</th>
</tr>
<?php
}

$votes = 0;

foreach ($list as $id => $proposal) {
    list ($days, $hours, $minutes) = proposal::formatDuration($proposal['duration']);
    if ($days == 0) {
        $end_date = $hours . ' hours, ' . $minutes . ' minutes';
    } else {
        $end_date = $days . ' days, ' . $hours . ' hours';
    }

    echo '<tr bgcolor="#cccccc">';
    echo '  <td>' . $proposal['name'] . '</td>';
    echo '  <td>' . $proposal['category'] . '</td>';
    echo '  <td>' . $proposal['summary'] . '</td>';
    echo '  <td>' . $end_date . '</td>';
    echo '  <td style="color: #00f">+' . $proposal['votes_pos'] . '<br /></td>';
    echo '  <td style="color: #f00">-' . $proposal['votes_neg'] . '</td>';
    echo '  <td>';
    echo make_link("info.php/" . $id, "Info/Vote");
    echo delim();
    echo make_link("edit.php/" . $id, "Edit");
    echo '  </td>';
    echo '</tr>';
}
?>

</table>

<?php $bb->end(); ?>

<h3>Quicklinks</h3>

<ul>
  <li><a href="propose.php">Propose</a> a new project</li>
  <li><a href="/admin/proposals.php">Administrate</a> the proposals (admins only)</li>
</ul>

<hr>

<h3>Voting Rules</h3>

<p>The rules for voting are quite simple: ...</p>

<?php
response_footer();
?>
