<?php // -*- C++ -*-

require_once "xmlrpc.inc";
require_once "xmlrpcs.inc";

ini_set("track_errors", true);

$GLOBALS['release_upload_sig'] =
array(
    array($GLOBALS['xmlrpcString'], // return type
          $GLOBALS['xmlrpcString'], // package
          $GLOBALS['xmlrpcString'], // version
          $GLOBALS['xmlrpcString'], // release notes
          $GLOBALS['xmlrpcBase64'], // tar ball
          $GLOBALS['xmlrpcString']),// md5sum
    );
$GLOBALS['release_upload_doc'] = "Upload a new release for a package.";

function release_upload($m)
{
    global $_return_value, $xmlrpcerruser, $xmlrpcBoolean, $dbh, $auth_user;
    // parameters: package, version, releasenotes, tarball, md5sum
    // implicit: doneby, releasedate

    // 1. verify that user has access
    // 2. verify that package exists
    // 3. verify that version does not exist
    // 4. store tar ball to temp file
    // 5. verify md5 checksum

    // later: do lots of integrity checks on the tarball

    $mpackage  = $m->getParam(0);
    $mversion  = $m->getParam(1);
    $mrelnotes = $m->getParam(2);
    $mdistfile = $m->getParam(3);
    $mmd5sum   = $m->getParam(4);

    $package  = $mpackage->scalarval();
    $version  = $mversion->scalarval();
    $relnotes = $mrelnotes->scalarval();
    $distfile = $mdistfile->scalarval();
    $md5sum   = $mmd5sum->scalarval();

    // (1) verify that user has access
    auth_require(0);
    if (isset($_return_value)) return $_return_value;
    // XXX FIXME check acl table

    // (2) verify that package exists
    $test = $dbh->getOne("SELECT name FROM packages WHERE name = ?",
                         array($package));
    if (isset($_return_value)) return $_return_value;
    if (empty($test)) {
        return xmlrpc_error("nonexistant package: $package");
    }

    // (3) verify that version does not exist
    $test = $dbh->getOne("SELECT version FROM releases ".
                         "WHERE package = ? AND version = ?",
                         array($package, $version));
    if (isset($_return_value)) return $_return_value;
    if ($test) {
        return xmlrpc_error("already exists: $package $version");
    }

    // (4) store tar ball to temp file
    $tempfile = sprintf("%s/%s%s-%s.tgz",
                        PEAR_TARBALL_DIR, ".new.", $package,$version);
    $file = sprintf("%s/%s-%s.tgz", PEAR_TARBALL_DIR, $package,$version);
    $fp = @fopen($tempfile, "w");
    if (!$fp) {
        return xmlrpc_error($php_errormsg);
    }
    fwrite($fp, $distfile);
    fclose($fp);
    if (!@rename($tempfile, $file)) {
        return xmlrpc_error("rename failed: $php_errormsg");
    }

    // (5) verify MD5 checksum
    ob_start();
    readfile($file);
    $data = ob_get_contents();
    ob_end_clean();
    if (md5($data) != $md5sum) {
        return xmlrpc_error("bad md5 checksum");
    }

    // Update releases table
    $query = "INSERT INTO releases VALUES(?,?,?,?,?,?,?)";
    $sth = $dbh->prepare($query);
    $dbh->execute($sth, array($package, $version, $auth_user->handle,
                              gmdate('Y-m-d H:i'), $relnotes, $md5sum,
                              $file));
    if (isset($_return_value)) return $_return_value;

    // success
    return new xmlrpcresp(new xmlrpcval(1, $xmlrpcBoolean));
}

$s = new xmlrpc_server(array("release.upload" =>
                             array("function" => "release_upload",
                                   "signature" => $release_upload_sig,
                                   "docstring" => $release_upload_doc),
                                  ));

?>
