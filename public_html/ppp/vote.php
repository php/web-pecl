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

$printForm = true;

$_POST['id'] = $id = (int)basename($_SERVER['PHP_SELF']);

do {
    if (isset($_POST['submit']) && !empty($_POST['val']) && !empty($_POST['id'])) {
        $err = proposal::vote($_POST);
        $printForm = false;
        break;
    }
} while (0);

response_header('PEAR Package Proposals: Vote');
?>

<h2>PEAR Package Proposals</h2>

<h3>Voting</h3>

<?php
if (isset($err) && PEAR::isError($err)) {
    PEAR::raiseError("Error while voting: " . $err->getMessage());
} elseif (isset($err)) {
    echo "<b>Your vote has been successful.</b><br /><br />";
    print_link("/ppp/", "Back");
}

if ($printForm == true) {
?>

<p>If you want the package to be part of PEAR, you have to give it a
positive vote. If you do not want it to be part of PEAR, you have to
give it a negative vote.</p>

<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<input type="hidden" name="id" value="<?php echo $id; ?>" />

<?php
$pkg = proposal::listOne($id);
$bb = new BorderBox("Vote", "90%", "", 2, true);
$bb->horizHeadRow("Name:", $pkg['name']);
$bb->horizHeadRow("Author:", $pkg['user_name']);
$bb->horizHeadRow("Your vote:",
                  '<select name="val" size="1"><option value=""> -- select -- </option><option value="1">positive (+1)</option><option value="-1">negative (-1)</option></select>');
$bb->horizHeadRow("Comment (optional):", '<textarea cols="30" rows="4" name="comment"></textarea>'
                  . '<br /><small>You should especially enter something '
                  . 'here, if you have given the package a negative '
                  . 'vote. (Up to 1000 chars)</small>');
$bb->horizHeadRow('<input name="submit" type="submit" />');
$bb->end();
?>

</form>
<br /><a href="list.php">Back</a>

<p>Note: During the voting process, we will try to set a cookie on your
system. This cookie is harmless and is just there to prevent you from
voting more than once for the same package.<br />Because we are aware
of the fact that you probably know, how to circumvent more complex
precautions, we do not specifically monitor voting activities. But we
appeal to your common sense and hope that you do not subvert this
service by voting multiple times.</p>

<?php
}

response_footer();
?>