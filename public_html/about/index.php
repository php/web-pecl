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
  | Authors: Martin Jansen <mj@php.net>                                  |
  +----------------------------------------------------------------------+
*/

response_header("About this site");
?>

<h1>About this site</h1>

<p>This site has been created and is maintained by a number of people,
which are listed on the <?php echo make_link("/credits.php", "credits page"); ?>.
If you would like to contact them, you can write to
<?php echo make_mailto_link("php-webmaster@lists.php.net"); ?>.</p>

<p>It has been built with <?php echo make_link("https://httpd.apache.org/", "Apache"); ?>,
<?php echo make_link("https://php.net/", "PHP"); ?>,
<?php echo make_link("https://www.mysql.com/", "MySQL"); ?> and some
(as you might have guessed) PEAR packages. The source code of the website is
<?php echo make_link("https://git.php.net/?p=web/pecl.git", "available via git"); ?>.
</p>

<p>Read the <?php echo make_link("privacy.php", "privacy policy"); ?>.</p>

<?php

// PDO connection
require_once __DIR__.'/../../src/Database.php';
use App\Database;

$dsn = 'mysql:host='.PECL_DB_HOST.';dbname='.PECL_DB_NAME.';charset=utf8';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $database = new Database($dsn, PECL_DB_USER, PECL_DB_PASSWORD, $options);

    $sql = "SELECT COUNT(*) FROM packages WHERE package_type = 'pecl'";

    if ($res = $database->query($sql)) {
        echo '<p>Total number of packages: '.$res->fetchColumn().'</p>';
    }
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

response_footer();
