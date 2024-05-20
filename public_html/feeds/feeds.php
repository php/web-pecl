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
  | Authors: Pierre-Alain Joye <pajoye@php.net>                          |
  +----------------------------------------------------------------------+
*/

use App\Entity\Category;
use App\Repository\ReleaseRepository;
use App\User;

require_once __DIR__.'/../../include/pear-prepend.php';

$releaseRepository = $container->get(ReleaseRepository::class);

function rssBailout() {
    header('HTTP/1.0 404 Not Found');
    echo 'The requested URL '.$_SERVER['REQUEST_URI'].' was not found on this server.';
    exit();
}

$urlRedirect = isset($_SERVER['REDIRECT_SCRIPT_URL']) ? $_SERVER['REDIRECT_SCRIPT_URL'] : '';

if (!empty($urlRedirect)) {
    $urlRedirect = str_replace(['/feeds/', '.rss'], ['', ''], $urlRedirect);
    $elems = explode('_', $urlRedirect);
    $type = $elems[0];
    $argument = htmlentities(strip_tags(str_replace($type . '_', '', $urlRedirect)));
} else {
    $uri = $_GET['type'];
    $elems = explode('_', $uri);
    $type = $elems[0];
    $argument = htmlentities(strip_tags(str_replace($type.'_', '', $uri)));
}

switch ($type) {
    case 'latest':
        $items = $releaseRepository->findRecent(10);
        $channelTitle = 'PECL: Latest releases';
        $channelDescription = 'The latest releases in PECL.';
    break;

    case 'user':
        $user = $argument;

        if (!User::exists($user)) {
            rssBailout();
        }

        $name = User::info($user, 'name');
        $channelTitle = 'PECL: Latest releases for '.$user;
        $channelDescription = 'The latest releases for the developer '.$user.' ('.$name.')';
        $items = $releaseRepository->getRecentByUser($user, 10);
    break;

    case 'pkg':
        $package = $argument;

        if (false === $packageEntity->isValid($package)) {
            rssBailout();
        }

        $channelTitle = 'Latest releases';
        $channelDescription = 'The latest releases for the package '.$package;
        $items = $releaseRepository->findRecentByPackageName($package, 10);
    break;

    case 'cat':
        $categoryName = $argument;

        if (false === $container->get(Category::class)->isValid($categoryName)) {
            rssBailout();
        }

        $channelTitle = 'PECL: Latest releases in category '.$categoryName;
        $channelDescription = 'The latest releases in the category '.$categoryName;
        $items = $releaseRepository->findRecentByCategoryName($categoryName, 10);
    break;

    default:
        rssBailout();
    break;
}

if (!is_array($items) || 0 === count($items)) {
    rssBailout();
}

// Override empty links
foreach ($items as $key => $item) {
    if (!isset($item['link'])) {
        $items[$key]['link'] = $container->get('scheme').'://'.$container->get('host').'/package-changelog.php?package='.$item['name'].'&amp;release='.$item['version'];
    }
}

header('Content-Type: text/xml; charset=utf-8');

echo $template->render('pages/feeds/feeds.php', [
    'url'                => $container->get('scheme').'://'.$container->get('host'),
    'items'              => $items,
    'channelTitle'       => $channelTitle,
    'channelDescription' => $channelDescription,
]);
