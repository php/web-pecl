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

use App\User;

/**
 * On 404 error this will search for a package with the same
 * name as the requested document. Thus enabling urls such as:
 *
 * https://pecl.php.net/operator
 */

// Requesting something like /~foobar will redirect to the account information
// page of the user "foobar".
if (strlen($_SERVER['REDIRECT_URL']) > 0 && $_SERVER['REDIRECT_URL']{1} == '~') {
    $user = substr($_SERVER['REDIRECT_URL'], 2);

    if (preg_match($config->get('valid_usernames_regex'), $user) && User::exists($user)) {
        localRedirect("/user/" . urlencode($user));
    }
}

$pkg = strtr($_SERVER['REDIRECT_URL'], '-','_');

// Check strictly
$name = $packageEntity->info(basename($pkg), 'name');
if (!empty($name)) {
    localRedirect('/package/'.urlencode($name));
}

// Check less strictly if nothing has been found previously
$sql = "SELECT p.id, p.name, p.summary
        FROM packages p
        WHERE package_type = 'pecl' AND approved = 1 AND name LIKE ?
        ORDER BY p.name";
$term = "%" . basename($pkg) . "%";
$packages = $database->run($sql, [$term])->fetchAll();

if (count($packages) > 3) {
    $packages = [$packages[0], $packages[1], $packages[2]];
    $show_search_link = true;
} else {
    $show_search_link = false;
}

response_header("Error 404");
?>

<h2>Error 404 - document not found</h2>

<p>The requested document <i><?php echo htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES); ?></i> was not
found on this server.</p>

<?php if (is_array($packages) && count($packages) > 0) { ?>
    Searching the current list of packages for
    <i><?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'], ENT_QUOTES)); ?></i> included the
    following results:

    <ul>
    <?php foreach($packages as $p) { ?>
        <li>
            <a href="/packages/<?= urlencode($p['name']); ?>"><?= htmlspecialchars($p['name'], ENT_QUOTES); ?></a><br />
            <i><?php echo htmlspecialchars($p['summary'], ENT_QUOTES); ?></i><br /><br />
        </li>
    <?php } ?>
    </ul>

    <?php if($show_search_link) { ?>
        <p align="center">
            <a href="/package-search.php?pkg_name=<?= htmlspecialchars(basename($_SERVER['REQUEST_URI'], ENT_QUOTES)); ?>&amp;bool=AND&amp;submit=Search">View full search results...</a>
        </p>
<?php
    }
}
?>

<p>If you think that this error message is caused by an error in the
configuration of the server, please contact
<a href="mailto:pecl-dev@lists.php.net">pecl-dev@lists.php.net</a>.

<?php

response_footer();
