<?php
$list = package::listAll();
//print_r($list);exit;
$items = array();
foreach ($list as $name => $data) {
    $items[$name] = $name;
}
?>
