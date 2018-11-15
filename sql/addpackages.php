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
  | Authors:                                                             |
  +----------------------------------------------------------------------+
*/

use App\Package;

print "Adding packages...\n";

// Drops all packages and adds sample packages
$dbh->expectError(DB_ERROR_NOSUCHTABLE);
$dbh->query('DELETE FROM packages');
$dbh->dropSequence('packages');
$dbh->popExpect();

$packages = '
Auth_HTTP;Authentication;mj;Methods for doing HTTP authentication
Auth;Authentication;mj;Methods for doing authentication
Config;Configuration;alexmerz;Class for managing configuration data
Crypt;Encryption;zyprexia;Several encryption classes
HTTP;HTTP;ssb;Miscellaneous HTTP utilities
HTTP_Uploader;HTTP;cox;Easy and secure managment of files submitted via HTML Forms
IO_Async;Networking;ssb;Backgrounded asynchronous socket IO
Log;Logging;jon;Logging utilities
Net_CheckIP;Networking;mj;Check the syntax of IPv4 adresses
Net_IPv6;Networking;alexmerz;Class to validate and to work with IPv6
Net_NNTP;Networking;kaltroft;Communicate with an NNTP server
Net_Ping;Networking;mj;Execute ping
Net_Whois;Networking;sn;The PEAR::Net_Whois class provides a tool for querying Whois Servers
PEAR;PEAR;ssb;PEAR base classes
Science_Chemistry;Science;jmcastagnetto;Classes to manipulated chemical objects: atoms, molecules, etc.
XML_fo2pdf;XML;chregu;Convert a xsl-fo file to pdf with the help of apache-fop
XML_RPC;XML;ssb;A PEAR-ified version of Useful inc\'s xmlrpc implementation for PHP.
XML_sql2xml;XML;chregu;Represent DB results with XML
XML_Tree;XML;sebastian;Represent XML data in a tree structure
';

$catmap = $dbh->getAssoc("SELECT name,id FROM categories");

foreach (explode("\n", $packages) as $line) {
    if (trim($line) == '') {
        continue;
    }
    list($name,$category,$lead,$summary) = explode(";", trim($line));
    if (empty($catmap[$category])) {
        print "Package: $name: skipped - unknown category `$category'\n";
        continue;
    } else {
        $catid = $catmap[$category];
    }
    Package::add([
        'name'        => $name,
        'type'        => 'pear',
        'license'     => 'PEAR License',
        'description' => '',
        'summary'     => $summary,
        'category'    => $catid,
        'lead'        => $lead
    ]);
    print "Package: $name\n";
}
