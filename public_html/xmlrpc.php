<?php // -*- C++ -*-

require_once "XML/RPC/Server.php";

ini_set("track_errors", true);

$release_upload_sig =
array(array($XML_RPC_String, // return type
            $XML_RPC_String, // package
            $XML_RPC_String, // version
            $XML_RPC_String, // release notes
            $XML_RPC_Base64, // tar ball
            $XML_RPC_String),// md5sum
      );
$release_upload_doc = "Upload a new release for a package.";

function xmlrpc_release_upload($m)
{
    global $_return_value, $XML_RPC_Boolean;
    // parameters: package, version, releasenotes, tarball, md5sum
    // implicit: doneby, releasedate

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
    auth_require();
    if (isset($_return_value)) return $_return_value;
    // XXX FIXME check acl table

    $ret = release_upload($package, $version, $relnotes, $distfile, $md5sum);
    if ($ret === true) {
        return new XML_RPC_Response(new XML_RPC_Value(1, $XML_RPC_Boolean));
    } else {
        return xmlrpc_error($ret);
    }
}

$s = new XML_RPC_Server(array("release.upload" =>
                              array("function" => "xmlrpc_release_upload",
                                    "signature" => $release_upload_sig,
                                    "docstring" => $release_upload_doc),
                              ));

?>
