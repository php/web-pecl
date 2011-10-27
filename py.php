<?php 
include_once 'phar:///home/pierre/pyrus.phar/PEAR2_Pyrus-2.0.0a3/php/PEAR2/Autoload.php';
try {
		  $package = new PEAR2\Pyrus\Package('/home/pierre/temp/APC-3.1.9.tgz');
} catch (Exception $e) {
		  echo $e->getMessage();
}

echo $package->version['release'] . "\n";
echo $package->version['api'] . "\n";
echo $package->channel . "\n";
echo $package->stability['release'] . "\n";
echo $package->stability['api'] . "\n";
echo $package->stability['api'] . "\n";
echo $package->attribs['version'] . "\n";
echo $package->name . "\n";

exit();
$file = '/home/pierre/repo/php/src/pecl/zip/trunk/package.xml';
try {
	$package = new PEAR2\Pyrus\Package('/home/pierre/repo/php/src/pecl/zip/trunk/package.xml');
} catch (Exception $e) {
	echo $e->getMessage();
}

$dom = new DOMDocument;
$dom->load($file);
libxml_use_internal_errors(true);
var_dump($dom->validate());
var_dump(libxml_get_errors());
