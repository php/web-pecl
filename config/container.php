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
  | Authors: Peter Kokot <petk@php.net>                                  |
  +----------------------------------------------------------------------+
*/

/**
 * Container initialization. Each service is created using Container::set()
 * method and a callable argument for convenience of future customizations or
 * adjustments beyond the scope of this container. See documentation for more
 * information.
 */

use App\Container\Container;

$container = new Container(include __DIR__.'/parameters.php');

$container->set(App\Database\Adapter::class, function ($c) {
    $pdoDsn = 'mysql:host='.$c->get('db_host').';dbname='.$c->get('db_name').';charset=utf8';

    $databaseAdapter = new App\Database\Adapter();
    $databaseAdapter->setDsn($pdoDsn);
    $databaseAdapter->setUsername($c->get('db_username'));
    $databaseAdapter->setPassword($c->get('db_password'));

    return $databaseAdapter;
});

$container->set(App\Database::class, function ($c) {
    return new App\Database($c->get(App\Database\Adapter::class)->getInstance());
});

$container->set(App\Utils\Filesystem::class, function ($c) {
    return new App\Utils\Filesystem();
});

$container->set(App\Utils\FormatDate::class, function ($c) {
    return new App\Utils\FormatDate();
});

$container->set(App\Utils\ImageSize::class, function ($c) {
    return new App\Utils\ImageSize();
});

$container->set(App\Auth::class, function ($c) {
    $auth = new App\Auth($c->get(App\Database::class), $c->get(App\Karma::class));
    $auth->setTmpDir($c->get('tmp_dir'));
    $auth->initSession();

    return $auth;
});

$container->set('auth_user', function ($c) {
    return $c->get(App\Auth::class)->initUser();
});

$container->set('last_updated', function ($c) {
    $tmp = filectime($_SERVER['SCRIPT_FILENAME']);

    return date('D M d H:i:s Y', $tmp - date('Z', $tmp)).' UTC';
});

$container->set(App\Template\Engine::class, function ($c) {
    $template = new App\Template\Engine(__DIR__.'/../templates');

    $template->register('getImageSize', [$c->get(App\Utils\ImageSize::class), 'getSize']);
    $template->register('formatDateToUtc', [$c->get(App\Utils\FormatDate::class), 'utc']);
    $template->register('nl2br', function ($content) {
        return str_replace('&NewLine;', '<br>', nl2br($content));
    });

    $template->assign([
        'scheme' => $c->get('scheme'),
        'host' => $c->get('host'),
        'auth' => $c->get(App\Auth::class),
        'lastUpdated' => $c->get('last_updated'),
        'onloadInlineJavaScript' => isset($GLOBALS['ONLOAD']) ? $GLOBALS['ONLOAD'] : '',
        'authUser' => $c->get('auth_user'),
    ]);

    return $template;
});

$container->set(App\Repository\AgregatedPackageStatsRepository::class, function ($c) {
    return new App\Repository\AgregatedPackageStatsRepository($c->get(App\Database::class));
});

$container->set(App\Repository\CategoryRepository::class, function ($c) {
    return new App\Repository\CategoryRepository($c->get(App\Database::class));
});

$container->set(App\Repository\CvsAclRepository::class, function ($c) {
    return new App\Repository\CvsAclRepository($c->get(App\Database::class));
});

$container->set(App\Repository\NoteRepository::class, function ($c) {
    return new App\Repository\NoteRepository($c->get(App\Database::class));
});

$container->set(App\Repository\PackageRepository::class, function ($c) {
    return new App\Repository\PackageRepository($c->get(App\Database::class));
});

$container->set(App\Repository\ReleaseRepository::class, function ($c) {
    return new App\Repository\ReleaseRepository($c->get(App\Database::class));
});

$container->set(App\Repository\UserRepository::class, function ($c) {
    return new App\Repository\UserRepository($c->get(App\Database::class));
});

$container->set(App\Karma::class, function ($c) {
    return new App\Karma($c->get(App\Database::class));
});

$container->set(App\Rest::class, function ($c) {
    $rest = new App\Rest($c->get(App\Database::class), $c->get(App\Utils\Filesystem::class));

    $rest->setDirectory($c->get('rest_dir'));
    $rest->setScheme($c->get('scheme'));
    $rest->setHost($c->get('host'));
    $rest->setCategoryRepository($c->get(App\Repository\CategoryRepository::class));
    $rest->setPackageRepository($c->get(App\Repository\PackageRepository::class));
    $rest->setUserRepository($c->get(App\Repository\UserRepository::class));

    return $rest;
});

$container->set(App\PackageDll::class, function ($c) {
    return new App\PackageDll($c->get('tmp_dir'));
});

$container->set(App\Entity\Category::class, function ($c) {
    $category = new App\Entity\Category();
    $category->setDatabase($c->get(App\Database::class));
    $category->setRest($c->get(App\Rest::class));

    return $category;
});

$container->set(App\Entity\Package::class, function ($c) {
    $packageEntity = new App\Entity\Package();
    $packageEntity->setDatabase($c->get(App\Database::class));
    $packageEntity->setRest($c->get(App\Rest::class));

    return $packageEntity;
});

$container->set(App\Release::class, function ($c) {
    $release = new App\Release();
    $release->setDatabase($c->get(App\Database::class));
    $release->setAuthUser($c->get('auth_user'));
    $release->setRest($c->get(App\Rest::class));
    $release->setPackagesDir($c->get('packages_dir'));
    $release->setPackage($c->get(App\Entity\Package::class));

    return $release;
});

$container->set(App\Utils\DependenciesFixer::class, function ($c) {
    return new App\Utils\DependenciesFixer($c->get(App\Database::class));
});

$container->set(App\Utils\Licenser::class, function ($c) {
    return new App\Utils\Licenser();
});

$container->set(App\Utils\Breadcrumbs::class, function ($c) {
    return new App\Utils\Breadcrumbs($c->get(App\Database::class));
});

return $container;
