<?php

$page = new PeclPage('/developer/page_developer.html');
$page->title = 'PECL Developer Area:: My Packages';
$page->setTemplate(PECL_TEMPLATE_DIR . '/developer/pacakges.html');
$page->render();
echo $page->html;