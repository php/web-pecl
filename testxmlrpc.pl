#!/usr/bin/perl
#
# This example is written in Perl just to test interoperability.
#

use XMLRPC::Lite;

$result = XMLRPC::Lite
  -> proxy('http://pear.localdomain/xmlrpc.php')
  -> call('package.new',
	  {'name' => 'Test_Package',
	   'category' => 'File System',
	   'license' => 'Test License',
	   'summary' => 'Test Summary',
	   'description' => 'Test Description',
	   'lead' => 'ssb',
	  })
  -> result;

print "package.new result=$result\n";
