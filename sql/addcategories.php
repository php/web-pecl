<?php
// Drops all categories and adds sample categories
error_reporting(E_ALL);
require_once 'DB.php';
require_once 'DB/storage.php';
require_once '../include/pear-database.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);
$dbh = DB::connect('mysql://pear:pear@localhost/pear');
$dbh->query('DELETE FROM categories');
$categories = array(
    'Benchmark' => '',
    'Cache'     => '',
    'Console'   => '',
    'Crypt'     => '',
    'DB'        => '',
    'Date'      => '',
    'File'      => '',
    'HTML'      => '',
    'HTTP'      => '',
    'Image'     => '',
    'Log'       => '',
    'Mail'      => '',
    'Math'      => '',
    'Net'       => '',
    'Numbers'   => '',
    'Payment'   => '',
    'Schedule'  => '',
    'XML'       => ''
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