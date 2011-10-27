<?php

$q = filter_input(INPUT_GET, 'term');
if (!$q) {
    return;
}
$handle_list = json_decode(file_get_contents(SVN_USERLIST));
$res = array();
foreach ($handle_list as $handle => $name) {
	if (strpos(strtolower($handle), $q) !== false) {
        $res[] = array(
            'label' => $name . '(' . $handle . ')',
            'value' => array(
                            'gravatar_id' => md5($handle . '@php.net'),
                            'handle'      => $handle,
                            'name'        => $name,
                        )
        );
	}
}

echo json_encode($res);


