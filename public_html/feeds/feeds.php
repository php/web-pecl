<?php

/*
  +----------------------------------------------------------------------+
  | The PECL website                                                     |
  +----------------------------------------------------------------------+
  | Copyright (c) 1999-2018 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | https://php.net/license/3_01.txt                                     |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors: Pierre-Alain Joye <pajoye@php.net>                          |
  +----------------------------------------------------------------------+
*/

function rss_bailout() {
    header('HTTP/1.0 404 Not Found');
    echo "<h1>The requested URL " . (($_SERVER['REQUEST_URI'])) . " was not found on this server.</h1>";
    exit();
}

// If file is given, the file will be used to store the rss feed
function rss_create($items, $channel_title, $channel_description, $dest_file=false) {
    if (is_array($items) && count($items)>0) {

        $rss_top = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns="http://purl.org/rss/1.0/" xmlns:dc="http://purl.org/dc/elements/1.1/">
    <channel rdf:about="http://pecl.php.net/">
    <link>http://pecl.php.net/</link>
    <dc:creator>php-webmaster@lists.php.net</dc:creator>
    <dc:publisher>php-webmaster@lists.php.net</dc:publisher>
    <dc:language>en-us</dc:language>
EOT;

        $items_xml = "<items>
<rdf:Seq>";
        $item_entries = '';

        foreach ($items as $item) {
            $date = date("Y-m-d\TH:i:s-05:00", strtotime($item['releasedate']));

            // Allows to override the default link
            if (!isset($item['link'])) {
                $url = 'http://' . PEAR_CHANNELNAME . '/package-changelog.php?package=' . $item['name'] . '&amp;release=' . $item['version'];
            } else {
                $url = $item['link'];
            }

            if (!empty($item['version'])) {
                $title = $item['name'] . ' ' . $item['version'];
            } else {
                $title = $item['name'];
            }

            $items_xml .= '<rdf:li rdf:resource="' . $url . '"/>' . "\n";
            $item_entries .= "<item rdf:about=" . '"' .$url . '"' . ">
<title>$title</title>
    <link>$url</link>
    <description>" .  htmlspecialchars($item['releasenotes']) ."
</description>
<dc:date>$date</dc:date>
</item>";
            $item_entries .= "";
        }

        $items_xml .= "</rdf:Seq>
</items>\n";

        $rss_feed = $rss_top . $items_xml ."
<title>$channel_title</title>
<description>$channel_description</description>
</channel>
$item_entries
</rdf:RDF>";

        // Lock free write, thx rasmus for the tip
        if($dest_file && (!file_exists($dest_file) || filemtime($dest_file) < (time()-$timeout))) {
            $stream = fopen($url,'r');
            $tmpf = tempnam('/tmp','YWS');

            // Note the direct write from the stream here
            file_put_contents($tmpf, $stream);
            fclose($stream);
            rename($tmpf, $dest_file);
        }
        header("Content-Type: text/xml; charset=utf-8");
        echo $rss_feed;
    } else {
        rss_bailout();
    }
}

$url_redirect = isset($_SERVER['REDIRECT_SCRIPT_URL']) ? $_SERVER['REDIRECT_SCRIPT_URL'] : '';

if (!empty($url_redirect)) {
    $url_redirect = str_replace(['/feeds/', '.rss'], ['', ''], $url_redirect);
    $elems = explode('_', $url_redirect);
    $type = $elems[0];
    $argument = htmlentities(strip_tags(str_replace($type . '_', '', $url_redirect)));
} else {
    $uri = $_GET['type'];
    $elems = explode('_', $uri);
    $type = $elems[0];
    $argument = htmlentities(strip_tags(str_replace($type . '_', '', $uri)));
}
if (PEAR_CHANNELNAME=='pecl.php.net') {
    $channel_base = "PECL";
} else {
    $channel_base = "PEAR";
}

switch ($type) {
    case 'latest':
        $items = Release::getRecent(10);
        $channel_title = $channel_base . ': Latest releases';
        $channel_description = 'The latest releases in ' . $channel_base . '.';
        break;

    case 'user':
        $user = $argument;
        if (!User::exists($user)) {
            rss_bailout();
        }

        $name = User::info($user, "name");
        $channel_title = $channel_base . ": Latest releases for " . $user;
        $channel_description = "The latest releases for the developer " . $user . " (" . $name['name'] . ")";
        $items = User::getRecentReleases($user);
        break;

    case 'pkg':
        $package = $argument;
        if (Package::isValid($package) == false) {
            rss_bailout();
            return PEAR::raiseError("The requested URL " . $_SERVER['REQUEST_URI'] . " was not found on this server.");
        }

        $channel_title = "Latest releases";
        $channel_description = "The latest releases for the package " . $package;

        $items = Package::getRecent(10, $package);
        break;

    case 'cat':
        $category = $argument;
        if (Category::isValid($category) == false) {
            rss_bailout();
        }

        $channel_title = $channel_base . ": Latest releases in category " . $category;
        $channel_description = "The latest releases in the category " . $category;

        $items = Category::getRecent(10, $category);
        break;

    case 'bugs':
        // To be done, new bug system supports it
        rss_bailout();
        break;

    default:
        rss_bailout();
        break;
}

// We do not use yet static files. It will be activated with the new backends.
// $file = __DIR__ . '/' .  $type . '_' . $argument . '.rss';
$file = false;
rss_create($items, $channel_title, $channel_description, $file);
