<?php
include __DIR__ . '/../include/pear-config.php';
$opts = array('ignore_errors' => true);
$ctx = stream_context_create(array('http' => $opts));
$token = getenv('USER_TOKEN');

$retval = file_get_contents('https://master.php.net/fetch/allusers.php?&token=' . rawurlencode($token), false, $ctx);

if (!$retval) {
    echo "error while processing request. no content returned";
    exit(1);
}

$json = json_decode($retval, true);

if (!is_array($json)) {
    echo "error while processing request. json decode fails";
    exit(1);
}

if (isset($json['error'])) {
    echo "error while processing request. error in query";
    exit(1);
}

$result = array();
foreach ($json as $entry) {
    $result[$entry['username']] = $entry['name'];
}
$json = json_encode($result);
file_put_contents(SVN_USERLIST, $json);
exit(0);
