<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001 The PHP Group                                     |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors:                                                             |
   +----------------------------------------------------------------------+
   $Id$
*/

/**
* On 404 error this will search for a package with the same
* name as the requested document. Thus enabling urls such as:
*
* http://pear.php.net/Mail_Mime
*/

$pinfo_url = '/package-info.php?pacid=';
$packages = $dbh->getAll($sql = sprintf("SELECT p.id, p.name, p.summary
                                           FROM packages p
                                          WHERE name LIKE '%%%s%%'
                                       ORDER BY p.name", basename($_SERVER['REDIRECT_URL'])), DB_FETCHMODE_ASSOC);

if (count($packages) == 1) {
	localRedirect($pinfo_url . $packages[0]['id']);

} elseif (count($packages) > 3) {
	$packages = array($packages[0], $packages[1], $packages[2]);
	$show_search_link = true;

} else {
	$show_search_link = false;
}

response_header("Error 404");
?>

<h2>Error 404 - document not found</h2>

<p>The requested document <i><?php echo $_SERVER['REQUEST_URI']; ?></i> was not
found on this server.</p>

<?if($packages):?>
	Searching the current list of packages for <i><?=basename($_SERVER['REQUEST_URI'])?></i> included the following results:
	
	<ul>
	<?foreach($packages as $p):?>
		<li>
			<?=make_link(getURL($pinfo_url . $p['id']), $p['name'])?><br />
			<i><?=$p['summary']?></i><br /><br />
		</li>
	<?endforeach?>
	</ul>
	
	<?if($show_search_link):?>
		<p align="center">
			<?=make_link(getURL('/package-search.php?pkg_name=' . basename($_SERVER['REQUEST_URI']) . '&bool=AND&submit=Search'), 'View full search results...')?>
		</p>
	<?endif?>
<?endif?>

<p>If you think that this error message is caused by an error in the
configuration of the server, please contact
<?php echo make_mailto_link("pear-webmaster@php.net"); ?>.

<?php response_footer(); ?>
