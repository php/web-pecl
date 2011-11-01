<?php

$sql = 'select id, packages.name, role from maintains,packages where packages.id = maintains.package AND handle=' .
       $dbh->quote($auth_user->handle) . 'ORDER BY name';
$my_package = $dbh->getAll($sql, NULL, DB_FETCHMODE_OBJECT);

$db_bug = DB::connect(BUG_DATABASE_DSN);
$sql = 'select id from bugdb where assign=' . $dbh->quote($auth_user->handle) . " and status not in ('bogus', 'closed', 'spam', 'wont fix', 'No feedback', 'feedback');";
$all_assigned_bugs = $db_bug->getAll($sql, NULL, DB_FETCHMODE_OBJECT);

$data = array(
    'my_package'  => $my_package,
    'my_bug_php'  => $my_bug_php,
    'my_bug_pecl' => $my_bug_pecl,
);

$page = new PeclPage('developer/page_developer.html');
$page->title = 'Developer Area - home';
$page->setTemplate(PECL_TEMPLATE_DIR . '/developer/home.html');
$page->jquery = true;
$page->addJsSrc('/js/jquery.drag.drop.js');
$page->addStyle('/js/jquery.drag.drop.css');
$page->addJsSrc('/js/release-upload.js');
$page->addData($data);
$page->render();
echo $page->html;


