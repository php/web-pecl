<?php

// Drops all categories and adds sample categories

error_reporting(E_ALL);
require_once 'DB.php';
require_once 'DB/storage.php';
require_once '../include/pear-database.php';

if (empty($dbh)) {
    $dbh = DB::connect('mysql://pear:pear@localhost/pear');
    $dbh->setErrorHandling(PEAR_ERROR_DIE);
}

$dbh->query('DELETE FROM categories');
$categories = array(
    'Benchmarking'  => '',
    'Caching'       => '',
    'Console'       => '',
    'Encryption'    => '',
    'Database'      => '',
    'Date and Time' => '',
    'File System'   => '',
    'HTML'          => '',
    'HTTP'          => '',
    'Images'        => '',
    'Logging'       => '',
    'Mail'          => '',
    'Math'          => '',
    'Networking'    => '',
    'Numbers'       => '',
    'Payment'       => '',
    'Scheduling'    => '',
    'XML'           => ''
);

foreach($categories as $name => $desc) {
    add_category(array('name' => $name, 'desc' => $desc));
}
/*$sql = 'select * from categories order by id';
$res = $dbh->query($sql);
echo "-------------------\n";
while($res->fetchInto($row, DB_FETCHMODE_ASSOC)) {
    foreach($row as $field => $value) {
        echo "$field => $value\n";
    }
    echo "-------------------\n";
}*/
?>