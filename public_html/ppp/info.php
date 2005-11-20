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

// Package data
$id = (int)basename($_SERVER['PHP_SELF']);

if ($id != 0) {
    $pkg = proposal::listOne($id);
} else {
    response_header("Error");
    PEAR::raiseError('No package selected');
    response_footer();
    exit();
}
response_header('PEAR Package Proposals: Info');
?>

<h2>PEAR Package Proposals</h2>

<h3>Proposal information</h3>

<?php
// {{{ "Package Information" box

$source_links = explode("\n", $pkg['source_links']);
foreach ($source_links as $link) {
    $links[] = make_link($link);
}

$bb = new BorderBox("Package Information", "90%", "", 2, true);

$bb->horizHeadRow("Name", $pkg['name']);
$bb->horizHeadRow("Summary", $pkg['summary']);
$bb->horizHeadRow("Description", nl2br($pkg['description']));
$bb->horizHeadRow(".phps files", implode("<br />", $links));
$bb->horizHeadRow("Developer", $pkg['user_name'] . " &lt;" . $pkg['email'] . "&gt;");
$bb->horizHeadRow("Additional homepage", make_link($pkg['homepage']));
$bb->horizHeadRow("Proposed on", $pkg['date_created']);

$bb->end();

echo "<p><a href=\"/ppp/vote.php/" . $id . "\">Vote</a> for this proposal.</p>";
echo "<p><a href=\"/ppp/\">Back</a></p>";
?>

<p>If you want to discuss issues about this proposal, please send a
mail to the author and CC: this message to pear-dev@lists.php.net.</p>

<?php
if (0) {
?>
Alternatively you can use the following form:</p>

<p><form method="post" action"<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
<input type="hidden" name="send" value="yes" />
<input type="hidden" name="id" value="<?php echo $id; ?>" />
<?php
$bb = new BorderBox("Start discussion", "90%", "", 2, true);
$bb->horizHeadRow("To:", $pkg['email']);
$bb->horizHeadRow("CC:", "pear-dev@lists.php.net");
$bb->horizHeadRow("Your email address:", "<input type=\"text\" name=\"my_email\" size=\"30\" />");
$bb->horizHeadRow("Subject:", "<input type=\"text\" name=\"subject\" size=\"30\" />");
$bb->horizHeadRow("Text:", "<textarea rows=\"10\" cols=\"45\" name=\"text\"></textarea>");
$bb->end();
?>
</form></p>

<?php
}

echo "<a href=\"edit.php/" . $id . "\">" . make_image("edit.gif") . "</a>";
// }}}
// {{{ page footer

response_footer();

// }}}
?>
