<?php # $Id$
response_header("CVS Branches and how they relate to PECL");
?>

<p>
If you have your PECL code in the PHP CVS repository, you need to be aware of
our branching convention.  The PHP core code is branched like this:
</p>

<pre>

------------------------------------ HEAD (development)
    \               \
     \               ---- PHP_5_0 (stable)
      --- PHP_4_3 (stable)
</pre>

<p>
All development takes place on HEAD, and important bug fixes are then backported to the stable branch(es) when appropriate.  In general, no new features are added to the stable branches.</p>

<p>
The convention for PECL extensions is to use a similar (but not identical!) scheme, in order to more easily coexist with some magic in the repository and with snapshot building tools:
</p>

<pre>

------------------------------------ HEAD (development)
    \               \
     \               ---- PECL_5_0 (stable)
      --- PECL_4_3 (stable)
</pre>

<p>
You only need to create/maintain these branches if the PHP core is branched for
release and HEAD (of both your code and the core) is changing in a backwards
incompatible way, so that it would not be possible to compile your code against
the release branch anymore.
</p>

<p>
You <b>don't</b> need to branch if your code on HEAD has appropriate
<tt>#ifdef</tt>'s to allow it to compile with the different PHP versions.
</p>

<p>
You also don't need to branch if you don't intend to maintain your extension
for those older versions; make sure you publish a version specific PECL package
before dropping support for a release.
</p>

<?php
response_footer();
?>
