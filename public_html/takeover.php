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
   | Authors:                                                             |
   +----------------------------------------------------------------------+
*/

$SIDEBAR_DATA='';

response_header("Support");
?>
<h1><a name="newmaint.takingover" id="newmaint.takingover">Taking over an unmaintained package</a></h1>

<p><a name="newmaint.takingover" id="newmaint.takingover">If you
want to become the new lead maintainer of a package that is marked
as unmaintained on the</a> <a href="http://pecl.php.net/" target=
"_top">PECL website</a>, the following section will explain to you
the necessary steps for this to happen.</p>
<ol type="1">
<li>
<p>The first thing is to inform the <a href=
"mailto:pecl-dev@lists.php.net" target="_top">PECL Developers 
mailing list</a> about your intention. If you have not been 
involved in PECL previously, it is a good idea to write a few 
words about you and your motivations.</p>
<p>Providing patches and tests may also help</p>
</li>
<li>
<p>
The PECL admins will then state whether you can take over the 
package or not along with an explanation of the decision. You can 
then <a href= "http://pecl.php.net/account-request.php"
target="_top">apply for an account</a> for the PECL website
unless you already have one. The PECL admins will have to grant
your account request and afterwards they will assign you as 
the new lead maintainer for the package.</p>
</li>
<li>
<p>
If the sources of the package are kept in the PHP git repository,
you will also need an account for this. You can sign up for it
on the <a href="http://www.php.net/git-php.php" target=
"_top">PHP website</a>. Please mention in the purpose field of the
request form that the PECL admins have told you to get an account, 
so that your request can be processed faster. git accounts are
managed by the PHP Group, so PECL unfortunately has only limited 
influence on this proces</p>
<div class="note">
<blockquote class="note">
<p><b>Note:</b> If you already have a git account for <tt class=
"literal">git.php.net</tt>, it is only necessary for you to get
additional <span class="QUOTE">"karma"</span> for the module where
the package resides. You can request this karma by sending an email
to the <a href="mailto:pecl-dev@lists.php.net" target="_top">PECL
Developers list</a> or simply tell us your account name during the 
request phase.</p>
</blockquote>
</div>
</li>
<li>
<p>If everything has worked out well, you should by now be the lead
maintainer of the previously unmaintained package. If not, don't
hesitate to ask the people on the <a href=
"mailto:pecl-dev@lists.php.net" target="_top">PECL Developers mailing list</a>
for help.</p>
</li>
</ol>
<?php
response_footer();
