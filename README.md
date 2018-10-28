# PHP Extension Community Library

[![PECL](/public_html/img/pecl.svg "PECL")](https://pecl.php.net)

The PHP Extension Community Library (PECL) (pronounced *pickle*) is a repository
for C and C++ extensions for compiling into the PHP language. This web application
is hosted online at [pecl.php.net](https://pecl.php.net).

## Index

* [About](#about)
* [Bugs and support](#bugs-and-support)
* [Contributing](#contributing)
* [Directory structure](#directory-structure)
* [Installation](#installation)
  * [1. Dependencies](#1-dependencies)
  * [2. Database](#2-database)
  * [3. Apache configuration](#3-apache-configuration)
* [Credits](#credits)
* [License and copyrights](#license-and-copyrights)

## About

PECL, formerly known as *PHP Extension Code Library*, has been initially started
under the PEAR umbrella. In October 2003 it has been renamed to
*PHP Extension Community Library* and it has evolved from the
[pearweb](https://github.com/pear/pearweb) application. Since then PECL related
services have been moved to online community repository
[pecl.php.net](https://pecl.php.net/) exclusively dedicated to extensions written
in C programming language to efficiently extend the PHP language. Today, many
widely used PECL extensions written in C and C++ are hosted and distributed via
PECL.

To learn more how to add new PECL extensions or how to install PECL extensions
using command line tools visit pecl.php.net and PHP manual.

## Bugs and support

Report bugs to [bugs.php.net](https://bugs.php.net/report.php). The PECL project
has a [mailing list](http://news.php.net/php.pecl.dev). More information about
support can be found at [pecl.php.net/support.php](https://pecl.php.net/support.php).

## Contributing

Git repository is located at [git.php.net](https://git.php.net/?p=web/pecl.git).
Contributions to the web application source code are most welcome by forking the
[GitHub mirror repository](https://github.com/php/web-pecl) and sending a pull
request.

```bash
git clone git@github.com:your-username/web-pecl
cd web-pecl
git checkout -b patch-1
git add .
git commit -m "Describe changes"
git push origin patch-1
```

A good practice is to also set the `upstream` remote in case the upstream master
branch updates. This way your master branch will track remote upstream master
branch of the root repository.

```bash
git checkout master
git remote add upstream git://github.com/php/web-pecl
git config branch.master.remote upstream
git pull --rebase
```

## Directory structure

Source code of this application is structured in the following directories:

```bash
<web-pecl>/
 ├─ .git/           # Git configuration and source directory
 ├─ cron/           # Various systems scripts to run periodically on server
 ├─ include/        # Application classes, functions, and configuration
 └─ public_html/    # Publicly accessible directory for online pecl.php.net
    ├─ css/         # Stylesheets
    ├─ img/         # Images
    ├─ javascript/  # JavaScript assets
    └─ ...
 ├─ script/         # Command line development tools and scripts
 ├─ sql/            # Database schema and development fixtures
 ├─ templates/      # Application templates
 └─ ...
```

## Installation

The pecl.php.net is written for PHP 5.5+, MySQL, and Apache 2.4.

### 1. Dependencies

To install PEAR dependencies:

```bash
pear channel-update pear.php.net
pear install --alldeps DB HTTP HTTP_Upload-1.0.0b4 HTML_Table Pager Net_URL HTML_Form
pear install --ignore-errors HTML_Form
```

### 2. Database

Historically the database of this web application is named `pear`. Initial
database schema and development data fixtures can be created using the scripts
in the `sql` directory.

To create the database, run `make create`. To remove the created database run
`make destroy` in the `sql` directory. On systems where `make` is not available,
for example, FreeBSD, use `gmake` instead.

### 3. Apache configuration

These are typical Apache directives you need to set up for a development site.
This installation has PEAR dependencies installed in `/usr/share/pear`.

```apacheconf
<VirtualHost *>
    ServerName pecl.localhost

    DocumentRoot /path/to/pecl/public_html
    DirectoryIndex index.php index.html

    php_value include_path .:/path/to/pecl/include:/usr/share/pear
    php_value auto_prepend_file pear-prepend.php

    ErrorDocument 404 /error/404.php

    Alias /package /path/to/pecl/public_html/package-info.php

    RewriteEngine On
    RewriteRule   /download-docs.php    /manual/index.php [R=301]
    RewriteRule   /rss.php              /feeds/latest.rss [R=301]

    # Rewrite rules for the RSS feeds
    RewriteRule   /feeds/(.+)\.rss$ /feeds/feeds.php?type=$1

    # Rewrite rules for the account info /user/handle
    RewriteRule   /user/(.+)$ /account-info.php?handle=$1

    # Rewrite rule for account info /package/pkgname/version
    RewriteRule   /package/(.+)/(.+)/windows$ /package-info-win.php?package=$1&version=$2
    RewriteRule   /package/(.+)/(.+)$ /package-info.php?package=$1&version=$2
    RewriteRule   /package/(.+)$ /package-info.php?package=$1

    <Location /get>
        ForceType application/x-httpd-php
    </Location>

    <Location /manual>
        ErrorDocument 404 /error/404-manual.php
    </Location>
</VirtualHost>
```

## Credits

This page would not be possible without a continuous effort of maintainers of
PECL extensions, open source contributors, hosting infrastructure sponsors, and
people involved in maintaining this site. Big thanks to
[everyone involved](https://pecl.php.net/credits.php).

## License and copyrights

This repository is released under the [PHP license](LICENSE). Visit the
[copyright page](https://pecl.php.net/copyright.php) for more information.
