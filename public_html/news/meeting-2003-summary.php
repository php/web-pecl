<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2003 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Martin Jansen <mj@php.net>                                  |
   +----------------------------------------------------------------------+
   $Id$
*/

response_header();

?>
<h1>PEAR Meeting - Summary</h1>

<div style="margin-left:2em;margin-right:2em">

<h2>Introduction</h2>

<p>
This document aims at providing a comprehensive sum up of what was said
and decided at the PEAR meeting which took place on Friday, 10th, May 2003
in Amsterdam.
</p>

<p>
For the record it should be noted that some people were attending this
meeting in person while many others were participating via IRC and
listening/watching video and audio streams. We would like to thank
Jeroen Houben from <?php print_link("http://www.terena.nl/", "Terena"); ?> 
for prodiving the facilities.
</p>

<p>
The following topics were discussed (in no particular order): Quality,
Documentation, PFC (PEAR Foundation Classes), PEAR on windows,
PEAR Installer, PEAR Website, PHP 5, Promotion, Future developments.
</p>

<p>The following list explains every point in detail.</p>

<ol type="I">

<li>
<h3>Quality</h3>

<p>
A QA team will be formed. It will make sure that quality standards are met.
Those standards have yet to be defined more precisely; they should be measurable.
They include the following minimum requirements: inline PHPDoc comments,
proper summary of the package purpose and a detailed usage example. Further
quality criterias are more documentation, user and developer unit tests
(using any of PHPUnit or phpt), API stability etc.
</p>

<p>
Packages that follow more than the minimum requirements will be able to show
this transparently to the user through the PEAR website.
</p>

<ul>
  <li>
    PHP object-oriented APIs should follow the
    <?php print_link("/manual/en/standards.php", "PEAR coding standards"); ?>.
  </li>
  <li>
    Breaking backwards compatibility is only allowed in a new major
    version.
  </li>
</ul>

</li>
<li>

<h3>Package policy</h3>

<p>
Redundant packages are not allowed. Instead, merging and/or refactoring
with existing packages is expected.<br />
Packages have to adhere to the versioning scheme (all BC breaks require
a major version upgrade). There is no stable version below 1.0.
BC breaks below version 1.0 are allowed.<br />
An archive zone for deprecated package, aka "Siberia" should be created. This
will also make it clear that PECL is NOT Siberia.
</p>

</li>
<li>

<h3>Documentation</h3>

<p>
PEAR Coding Standards (CS) will include method naming conventions
(i.e.: methods which do similar things will be named the same across packages,
examples are connect, display, fetch, etc.).<br />
A documentation team will be formed to handle all related issues.<br />
The team is expected to :
</p>

<ul>
  <li>write tutorials on how to write documentation</li>
  <li>
    provide tools to make doc generation easy since Docbook is currently
    the standard format.
  </li>
  <li>
    ensure that this generation does not need any non-standard tool
    or services on pear.php.net to be made available so that people
    don't need so install this software locally.
  </li>
</ul>

<p>
<?php print_link("/package-info.php?package=PhpDocumentor", "PHPDocumentor"); ?> 
is now the official tool to generate API documentation.
</p>

</li>
<li>

<h3>PFC</h3>

<p>
Christian Stocker's proposal as found in the
<?php print_link("http://marc.theaimsgroup.com/?l=pear-dev&m=104612175324131&w=2", "RfC"); ?> 
will be used with the following changes:
</p>

Platform support:
<ul>
  <li>
    MacOS X is not a widely used platform yet, if someone can provide
    us an access to a machine running OS X, we can improve OS X support;
    it will then probably work as nicely as on *NIX.
  </li>
  <li>
    Solaris is a widely used platform
  </li>
</ul>

</li>

<p>Concurrent Packages:</p>

<p>
A "light" implementation of a package needs to be extended to provide
a richer set of features. Example cache_lite would have to extend cache,
i.e. "cache API extends cache_lite API".<br />
It was not yet decided if this "extends" means that the class itself will
have to be extended, but only that the interface needs to be "extended"
</p>

</li>
<li>

<h3>PEAR on Windows</h3>

<p>The PEAR Installer is now working on windows.</p>

</li>
<li>

<h3>PEAR group</h3>

<p>
A PEAR Group should be formed. Its size will be 5-9 people,
an uneven number will be chosen.<br />
It will be dynamic, people can join or leave it. What happens in that
case (especially regarding maintaining an uneven number) was not made.
The initial members are appointed by Stig. The Group will then regulate
itself. The Group can apply a veto on a package proposal. The veto
should  remain until the issues are solved. For example the latest
discussion on pear-dev about IT[X] vs. Sigma could have resulted in a
veto by the PEAR Group.
</p>

<p>The roles of this group are:</p>

<ul>
  <li>keep the PEAR "roadmap" and quality</li>
  <li>handle conflicts</li>
  <li>organize the PEAR project</li>
  <li>set standards</li>
</ul>

</li>
<li>

<h3>Website</h3>

<p>Website enhancements:</p>

<ul>
  <li>add votes and comments for packages</li>
  <li>
    Proposals and voting will also be handled though the website only,
    including discussions of the package, this will not be done 
    through the mailing list. Nevertheless the list will be
    cc'ed. QA and/or the PEAR Group can apply a veto.
  </li>
  <li>
    We may require a certain amount of votes form certain groups
    (developers with CVS account, QA team) in order for a proposal
    to pass.
  </li>
</ul>

</li>
<li>

<h3>Installer</h3>

<ul>
  <li>
    improve the version handling, we especially need to add a guide
    on version naming in the PEAR manual.
  </li>
  <li>PECL installer does not work on Windows</li>
  <li>
    PECL installer does not detect in an extension is loaded while
    upgrading it that creates a possible crash.
  </li>
  <li>
    Need of a check in the packager to verify if the version name is
    correct (that will be registered as an upgrade)
  </li>
  <li>Need of binaries handling for different platforms</li>
  <li>
    updating stables  should not be updated with lower "level"
    (beta, alpha) releases
  </li>
  <li>
    BC breaking releases should not be done automaticly (i.e.
    upgrade-all)
  </li>
  <li>
    Installation if older versions should not require the complete
    URL but only by adding the version number
  </li>
  <li>
    BC breaks require a major version increase
  </li>
</ul>

<p>Future plans:</p>

<ul>
  <li>mirror support</li>
  <li>automatic dependeny resolving through a local database</li>
  <li>move away from xml-rpc</li>
  <li>taking another look at an rpm based solution</li>
</ul>

</li>
<li>

<h3>Promotion</h3>

<p>
More and more magazines are publishing articles about PEAR, they even
approach us for writing for them.<br />
The International PHP Magazine (http://www.php-mag.net) will release articles
if they are older than a few issues and we are working on ways to make them
updateable. Also the Intl' PHP Magazine will distribute PEAR on the magazine
CD.<br />
Horde and Midgard are having closer looks at PEAR.<br />
The PEAR Weekly News are part of the promotion effort and should be
published again as soon as possible.
</p>

</li>
<li>

<h3>PHP5</h3>

<p>
We need either nested classes and/or namespaces with working import.
Well working exceptions are part of the requirements.
We have yet not found a good solution about having PHP 4 and PHP 5 code
in PEAR. We first have to figure out how ZE2 will look like. There is
still the idea of code morphing at packaging. (see the peardev archives), 
although in Stig's original proposal, he suggested morphing on the server.
</p>

</div>
<?php
response_footer();
?>
