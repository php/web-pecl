<?php
//
// This script will provoke different errors in the database backend
// and output code to insert into the DB_dbtype constructor.
//

require_once 'DB.php';

header("Content-type: text/plain");

switch ($type) {
    case 'mysql':
    default:
	$dsn = "mysql://pear@localhost/pear";
	break;
}

$dbh = DB::connect($dsn);
if (DB::isError($dbh)) {
    die("DB::connect failed: ".DB::errorMessage($dbh)."<BR>\n");
}

//
// A number of queries that are supposed to fail, and the
// DB error each query should return.
//
$error_queries = array(
    "SELECT * FROM fkjsfs"                    => 'DB_ERROR_NOSUCHTABLE',
    "SELECT * FROM users WHERE fooooo = 42"   => 'DB_ERROR_NOSUCHFIELD',
    "SELECT *= FROM users"                    => 'DB_ERROR_SYNTAX',
    "CREATE TABLE users ( id INTEGER )"       => 'DB_ERROR_CANNOT_CREATE',
    "INSERT INTO mytable VALUES(1)"           => 'DB_ERROR_ALREADY_EXISTS',
    "DROP TABLE nonexistant"                  => 'DB_ERROR_NOSUCHTABLE'
);

$errormap = array();

reset($error_queries);
while (list($query, $dberror) = each($error_queries)) {
    $sth = $dbh->query($query);
    if (DB::isError($sth)) {
	$errorcode = $dbh->errorNative();
	$error = is_string($errorcode) ? "'$errorcode'" : $errorcode;
	$errormap[$error] = $dberror;
    }
}

print "\$this->errorcode_map = array(\n";
reset($errormap);
$i = 1;
while (list($native, $dberror) = each($errormap)) {
    print "    $native => $dberror";
    if ($i++ < sizeof($errormap)) {
	print ",";
    }
    print "\n";
}
print ");\n";

?>
