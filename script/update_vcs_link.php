#!/usr/bin/env php
<?php
include __DIR__ . '/../include/pear-config.php';

$sql_movetosvn = "UPDATE packages SET cvs_link =
IF (cvs_link REGEXP('cvs.php.net\/cvs.php(.*)'),
	REPLACE(cvs_link, 'cvs.php.net/cvs.php', 'svn.php.net'),
	IF (cvs_link REGEXP('cvs.php.net\/viewvc.cgi(.*)'),
		REPLACE(cvs_link, 'cvs.php.net/viewvc.cgi', 'svn.php.net'),
		IF (cvs_link REGEXP('cvs.php.net\/pecl(.*)'),
			REPLACE(cvs_link, 'cvs.php.net', 'svn.php.net'),
			IF (cvs_link REGEXP('viewcvs.php.net\/viewvc.cgi(.*)'),
				REPLACE(cvs_link, 'viewcvs.php.net/viewvc.cgi', 'svn.php.net'),
				IF (cvs_link REGEXP('cvs.php.net\/viewcvs.cgi(.*)'),
					REPLACE(cvs_link, 'cvs.php.net/viewcvs.cgi', 'svn.php.net'),
					IF (cvs_link REGEXP('cvs.php.net\/php-src(.*)'),
						REPLACE(cvs_link, 'cvs.php.net/php-src', 'svn.php.net/php/php-src/trunk'),
						cvs_link
					)
				)
			)
		)
	)
)
where package_type='pecl' and cvs_link like '%cvs.php.net%';
";

$dh = new PDO(PECL_DB_DSN, PECL_DB_USER, PECL_DB_PASSWORD);

$res = $dh->query($sql_movetosvn);
if (!$res) {
	var_dump($dh->errorInfo());
}
