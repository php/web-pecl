<?php

Header("Content-type: text/plain");
$dbh->setErrorHandling(PEAR_ERROR_DIE);
$sth = $dbh->query("SELECT * FROM packages ORDER BY name");
$tree = array('' => array("children" => array()));;
while ($sth->fetchInto($row, DB_FETCHMODE_ASSOC) === DB_OK) {
    extract($row);
    if ($name == '') {
        continue;
    }
    $parent = ereg_replace('_[^_]+$', '', $name);
    if ($parent == $name) {
        $parent = '';
    }
    $tree[$parent]["children"][] = $name;
    $tree[$name]["parent"] = $parent;
}

visit($tree, '');

function visit(&$tree, $node) {
    static $visitno;
    if (empty($visitno)) {
        $visitno = 1;
    }
    print "start visit $node\n";
    $tree[$node]['leftvisit'] = $visitno++;
    if (isset($tree[$node]['children'])) {
        foreach ($tree[$node]['children'] as $cnode) {
            visit($tree, $cnode);
        }
    }
    $tree[$node]['rightvisit'] = $visitno++;
    print "end   visit $node\n";
}

foreach ($tree as $node => $data) {
    $l = $data["leftvisit"];
    $r = $data["rightvisit"];
    $query = "UPDATE packages SET leftvisit = $l, rightvisit = $r ".
        "WHERE name = '$node'";
    $dbh->query("UPDATE packages SET leftvisit = $l, rightvisit = $r ".
                "WHERE name = '$node'");
}

$from = $tree['Science_Chemistry']['leftvisit'];
$to = $tree['Science_Chemistry']['rightvisit'];
$test = $dbh->getAll("SELECT name FROM packages WHERE leftvisit <= $from ".
                     "AND rightvisit >= $to");
print "test="; var_dump($test);

?>
