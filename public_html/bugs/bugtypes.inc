<?php
$list = package::listAll();
$items = array();
foreach ($list as $name => $data) {
    $items[$name] = $name;
}
// Extra items should be added to
// include/functions.inc:get_bugtype_mail()
//
$items['Web Site']      = 'Web Site';
$items['Documentation'] = 'Documentation';
$items['Bug System']    = 'Bug System';
?>
