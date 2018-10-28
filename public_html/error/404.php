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
 * On 404 error this will search for a package with the same
 * name as the requested document. Thus enabling urls such as:
 *
 * https://pear.php.net/Mail_Mime
 */

/**
 * Requesting something like /~foobar will redirect to the account
 * information page of the user "foobar".
 */
if (strlen($_SERVER['REDIRECT_URL']) > 0 && $_SERVER['REDIRECT_URL']{1} == '~') {
    $user = substr($_SERVER['REDIRECT_URL'], 2);
    if (preg_match(PEAR_COMMON_USER_NAME_REGEX, $user) && user::exists($user)) {
        localRedirect("/user/" . urlencode($user));
    }
}

$pkg = strtr($_SERVER['REDIRECT_URL'], '-','_');
$pinfo_url = '/package/';

// Check strictly
$name = package::info(basename($pkg), 'name');
if (!DB::isError($name) && !empty($name)) {
    if (!empty($name)) {
        localRedirect($pinfo_url . $name);
    } else {
        $name = package::info(basename($pkg), 'name', true);
        if (!empty($name)) {
            header('HTTP/1.0 301 Moved Permanently');
            header('Location: https://pear.php.net/package/' . $name);
            header('Connection: close');
            exit();
        }
    }
}

// Check less strictly if nothing has been found previously
$sql = "SELECT p.id, p.name, p.summary
            FROM packages p
            WHERE package_type = 'pecl' AND approved = 1 AND name LIKE ?
            ORDER BY p.name";
$term = "%" . basename($pkg) . "%";
$packages = $dbh->getAll($sql, [$term], DB_FETCHMODE_ASSOC);

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
			<?php print_link(getURL($pinfo_url . $p['name']), $p['name']); ?><br />
			<i><?php echo $p['summary']; ?></i><br /><br />
		</li>
	<?php } ?>
	</ul>

	<?php if($show_search_link) { ?>
		<p align="center">
			<?php print_link(getURL('/package-search.php?pkg_name=' . htmlspecialchars(basename($_SERVER['REQUEST_URI'], ENT_QUOTES)) . '&amp;bool=AND&amp;submit=Search'), 'View full search results...'); ?>
		</p>
<?php
    }
}
?>

<p>If you think that this error message is caused by an error in the
configuration of the server, please contact
<?php echo make_mailto_link("pecl-dev@lists.php.net"); ?>.

<?php

response_footer();
