<?php // -*- C++ -*-

require_once "xmlrpc.inc";
require_once "xmlrpcs.inc";

$test_sig = array(array($xmlrpcString));
$test_doc = "Test XML-RPC method";
function test_wrp($m) {
    $marg = $m->getParam(0);
    $arg = $marg->scalarval();
}

$release_upload_sig = array(array($xmlrpcStruct, // return type
                                  $xmlrpcString, // package
                                  $xmlrpcString, // version
                                  $xmlrpcString, // release notes
                                  $xmlrpcBase64, // tar ball
                                  $xmlrpcString  // md5sum
    ));
$release_upload_doc = "Upload a new release for a package.";

function release_upload($m) {
    // parameters: package, version, releasenotes, tarball, md5sum
    // implicit: doneby, releasedate

    // 1. verify that package exists
    // 2. verify that user has access
    // 3. verify that version does not exist
    // 4. store tar ball to temp file
    // 5. verify md5 checksum

    // later: do lots of integrity checks on the tarball

    global $xmlrpcerruser;

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

    if (md5($distfile) != $md5sum) {
        return new xmlrpcresp(0, $xmlrpcerruser, "bad md5 checksum");
    }

    return new xmlrpcresp(new xmlrpcval("ok", "string"));
}

$s = new xmlrpc_server(array("release.upload" =>
                             array("function" => "release_upload",
                                   "signature" => $release_upload_sig,
                                   "docstring" => $release_upload_doc),
                                  ));

?>
