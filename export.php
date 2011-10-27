<?php
$data = json_decode(file_get_contents('new_id.txt'));
foreach ($data as $bug) {
	$a[$bug->id] = $bug->new_id;
}
var_export($a);
