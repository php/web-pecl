<?php

/*
  +----------------------------------------------------------------------+
  | The PECL website                                                     |
  +----------------------------------------------------------------------+
  | Copyright (c) 1999-2019 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | https://php.net/license/3_01.txt                                     |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors: Martin Jansen <mj@php.net>                                  |
  +----------------------------------------------------------------------+
*/

use App\Entity\Note;
use App\Entity\User as UserEntity;
use App\User;
use App\Auth;
use App\Repository\NoteRepository;
use App\Repository\UserRepository;

require_once __DIR__.'/../../include/pear-prepend.php';

// Restricted to administrators only.
$container->get(Auth::class)->secure(true);

$acreq = isset($_GET['acreq']) ? strip_tags(htmlspecialchars($_GET['acreq'], ENT_QUOTES)) : null;

$note = $container->get(Note::class);

$content = '';
// Adding and deleting notes.
if (!empty($_REQUEST['cmd'])) {
    if ($_REQUEST['cmd'] == "Add note" && !empty($_REQUEST['note']) && !empty($_REQUEST['key']) && !empty($_REQUEST['id'])) {
        $note->add($_REQUEST['key'], $_REQUEST['id'], $_REQUEST['note']);
        unset($_REQUEST['cmd']);
    } elseif ($_REQUEST['cmd'] == "Delete note" && !empty($_REQUEST['id'])) {
        // Delete note
        $note->remove($_REQUEST['id']);
    } elseif ($_REQUEST['cmd'] == "Open Account" && !empty($_REQUEST['uid'])) {
        //  Open account

        // Another hack to remove the temporary "purpose" field from the user's
        // "userinfo"
        if (User::activate($_REQUEST['uid'])) {
            $content .= '<p>Opened account '.htmlspecialchars($_REQUEST['uid'], ENT_QUOTES)."...</p>\n";
        }
    } elseif ($_REQUEST['cmd'] == "Reject Request" && !empty($_REQUEST['uid'])) {
        /// Reject account request
        if (is_array($_REQUEST['uid'])) {
            foreach ($_REQUEST['uid'] as $uid) {
                User::rejectRequest($uid, $_REQUEST['reason']);
                $content .= 'Account rejected: ' . $uid . '<br>';
            }

        } elseif (User::rejectRequest($_REQUEST['uid'], $_REQUEST['reason'])) {
            $content .= '<p>Rejected account request for '.htmlspecialchars($_REQUEST['uid'], ENT_QUOTES)."...</p>\n";
        }

    } elseif ($_REQUEST['cmd'] == "Delete Request" && !empty($_REQUEST['uid'])) {
        // Delete account request
        if (is_array($_REQUEST['uid'])) {
            foreach ($_REQUEST['uid'] as $uid) {
                User::remove($uid);
                $content .= 'Account request deleted: '.$uid.'<br>';
            }

        } elseif (User::remove($_REQUEST['uid'])) {
            $content .= '<p>Deleted account request for "'.htmlspecialchars($_REQUEST['uid'], ENT_QUOTES). '"...</p>';
        }
    }
}

$requser = new UserEntity($database, $acreq);

list($purpose, $moreinfo) = @unserialize($requser->get('userinfo'));

echo $template->render('pages/admin/index.php', [
    'display' => empty($requser->get('name')) ? false : true,
    'acreq' => $acreq,
    'requser' => $requser,
    'purpose' => $purpose,
    'moreinfo' => $moreinfo,
    'notes' => $container->get(NoteRepository::class)->getNotesByUser($requser->handle),
    'requests' => $container->get(UserRepository::class)->findRequests(),
    'content' => $content,
]);
