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

/**
 * Details about PECL accounts
 */

use App\BorderBox;
use App\Repository\NoteRepository;
use App\Repository\UserRepository;
use App\Repository\PackageRepository;
use App\Repository\CvsAclRepository;

$handle = filter_input(INPUT_GET, 'handle', FILTER_SANITIZE_STRING);

// Redirect to the accounts list if no handle was specified
if (empty($handle)) {
    localRedirect("/accounts.php");
}

$userRepository = new UserRepository($database);

$row = $userRepository->findByHandle($handle);

if (!$row) {
    // XXX: make_404();
    header('HTTP/1.0 404 Not Found');
    PEAR::raiseError("No account information found!");
}

response_header($row['name']);

print "<h1>" . $row['name'] . "</h1>\n";

print "<table border=\"0\" cellspacing=\"4\" cellpadding=\"0\">\n";
print "<tr><td valign=\"top\">\n";

$bb = new BorderBox("Account Details", "100%", "", 2, true);
$bb->horizHeadRow("Handle:", $handle);
$bb->horizHeadRow("Name:", $row['name']);
if ($row['showemail'] != 0) {
    $bb->horizHeadRow("Email:", "<a href=\"/account-mail.php?handle=" . $handle . "\">".str_replace(["@", "."], [" at ", " dot "], $row['email'])."</a>");
}
if ($row['homepage'] != "") {

    $url = parse_url($row['homepage']);
    if (empty($url['scheme'])) {
        $row['homepage'] = 'http://' . $row['homepage'];
    }

    $bb->horizHeadRow("Homepage:",
                      "<a href=\"$row[homepage]\" target=\"_blank\">".
                      "$row[homepage]</a></td>\n");
}

//XXX: Remove entirely?
//$bb->horizHeadRow("Registered since:", $row['created']);
$bb->horizHeadRow("Additional information:", empty($row['userinfo'])?"&nbsp;":$row['userinfo']);

$cvsAclRepository = new CvsAclRepository($database);
$access = $cvsAclRepository->getPathByUsername($handle);

$bb->horizHeadRow("VCS Access:", implode("<br />", $access));

if ($row['wishlist'] != "") {
    $bb->horizHeadRow("Wishlist:", '<a href="/wishlist.php/'.$row['handle'].'">Click here to be redirected.</a>');
}

if ($row['admin'] == 1) {
    $bb->fullRow("$row[name] is a PECL administrator.");
}

$bb->end();

print "</td><td valign=\"top\">\n";

$bb = new BorderBox("Maintaining These Packages:", "100%", "", 2, true);

$packageRepository = new PackageRepository($database);
$packages = $packageRepository->findPackagesMaintainedByHandle($handle);

if (isset($packages) && count($packages) > 0) {
    $bb->headRow("Package Name", "Role");

    foreach ($packages as $package) {
        $name = htmlspecialchars($package['name'], ENT_QUOTES);
        $role = htmlspecialchars($package['role'], ENT_QUOTES);
        $bb->plainRow('<a href="/package/'.$name.'">'.$name.'</a>', $role);
    }
}

$bb->end();

print "<br />\n";

$bb = new BorderBox("Notes for user $handle", '100%');
$noteRepository = new NoteRepository($database);
$notes = $noteRepository->getNotesByUser($handle);

if (!empty($notes)) {
    print "<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\">\n";
    foreach ($notes as $nid => $data) {
    print " <tr>\n";
    print "  <td>\n";
    print "   <b>{$data['nby']} {$data['ntime']}:</b>";
    print "<br />\n";
    print "   ".htmlspecialchars($data['note'], ENT_QUOTES)."\n";
    print "  </td>\n";
    print " </tr>\n";
    print " <tr><td>&nbsp;</td></tr>\n";
    }
    print "</table>\n";
} else {
    print "No notes.";
}
$bb->end();

print '<br><a href="/account-edit.php?handle='.htmlspecialchars($handle, ENT_QUOTES).'"><img src="/gifs/edit.gif" alt="Edit"></a>';

print "</td></tr></table>\n";

response_footer();
