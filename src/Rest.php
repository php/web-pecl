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

require_once 'PEAR/PackageFile/Parser/v2.php';
require_once 'PEAR/Config.php';
require_once __DIR__.'/Utils/Filesystem.php';
require_once __DIR__.'/Karma.php';

use App\Utils\Filesystem;

/**
 * The PECL REST API management service class.
 */
class Rest
{
    private $dir;
    private $dbh;
    private $filesystem;

    /**
     * Class constructor.
     */
    public function __construct($dir, $dbh, Filesystem $filesystem)
    {
        $this->dir = $dir;
        $this->dbh = $dbh;
        $this->filesystem = $filesystem;
    }

    /**
     * Regenerate all categories info.
     */
    public function saveAllCategories()
    {
        $extra = '/rest/';
        $cdir = $this->dir.'/c';

        if (!file_exists($cdir)) {
            mkdir($cdir, 0777, true);
            @chmod($cdir, 0777);
        }

        $categories = Category::listAll();
        $info = '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allcategories"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allcategories
    http://pear.php.net/dtd/rest.allcategories.xsd">
<ch>' . PEAR_CHANNELNAME . '</ch>
';
        foreach ($categories as $category) {
            $info .= ' <c xlink:href="' . $extra . 'c/' .
                urlencode(urlencode($category['name'])) .
                '/info.xml">' .
                htmlspecialchars(utf8_encode($category['name'])) . '</c>
';
        }

        $info .= '</a>';

        file_put_contents($cdir.'/categories.xml', $info);
        @chmod($cdir.'/categories.xml', 0666);
    }

    /**
     * Save category info.
     */
    public function saveCategory($category)
    {
        $extra = '/rest/';
        $cdir = $this->dir.'/c';

        if (!file_exists($cdir)) {
            mkdir($cdir, 0777, true);
            @chmod($cdir, 0777);
        }

        $category = $this->dbh->getAll('SELECT * FROM categories WHERE name = ?', [$category], DB_FETCHMODE_ASSOC);
        $category = $category[0];

        $categoryDir = $cdir.'/'.urlencode($category['name']);
        if (!file_exists($categoryDir)) {
            mkdir($categoryDir, 0777, true);
            @chmod($categoryDir, 0777);
        }

        $category['description'] = htmlspecialchars($category['description']);
        $info = '<?xml version="1.0" encoding="UTF-8" ?>
<c xmlns="http://pear.php.net/dtd/rest.category"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.category
    http://pear.php.net/dtd/rest.category.xsd">
 <n>' . htmlspecialchars($category['name']) . '</n>
 <c>' . PEAR_CHANNELNAME . '</c>
 <a>' . htmlspecialchars($category['name']) . '</a>
 <d>' . $category['description'] . '</d>
</c>';
        // category info
        file_put_contents($categoryDir.'/info.xml', $info);
        @chmod($categoryDir.'/info.xml', 0666);

        $list = '<?xml version="1.0" encoding="UTF-8" ?>
<l xmlns="http://pear.php.net/dtd/rest.categorypackages"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.categorypackages
    http://pear.php.net/dtd/rest.categorypackages.xsd">
';
        $query = "SELECT p.name AS name " .
            "FROM packages p, categories c " .
            "WHERE p.package_type = 'pecl' " .
            "AND p.category = c.id AND c.name = ? AND p.approved = 1";

        $sth = $this->dbh->getAll($query, [$category['name']], DB_FETCHMODE_ASSOC);

        foreach ($sth as $package) {
            $list .= ' <p xlink:href="' . $extra . 'p/' . strtolower($package['name']) . '">' .
                $package['name'] . '</p>
';
        }

        $list .= '</l>';
        // list packages in a category
        file_put_contents($categoryDir.'/packages.xml', $list);
        @chmod($categoryDir.'/packages.xml', 0666);
    }

    /**
     * Regenerate packages category info.
     */
    public function savePackagesCategory($category)
    {
        $cdir = $this->dir.'/c';

        if (!is_dir($cdir)) {
            return;
        }

        $pdir = $this->dir.'/p';
        $rdir = $this->dir.'/r';
        $packages = Category::listPackages($category);
        $fullpackageinfo = '<?xml version="1.0" encoding="UTF-8" ?>
<f xmlns="http://pear.php.net/dtd/rest.categorypackageinfo"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.categorypackageinfo
    http://pear.php.net/dtd/rest.categorypackageinfo.xsd">
';
        clearstatcache();
        foreach ($packages as $package) {
            if (!file_exists($pdir.'/'.strtolower($package['name']).'/info.xml')) {
                continue;
            }

            $fullpackageinfo .= '<pi>
';
            $contents = file_get_contents($pdir.'/'.strtolower($package['name']).'/info.xml');
            $fullpackageinfo .= '<p>' . substr($contents, strpos($contents, '<n>'));

            if (file_exists($rdir.'/'.strtolower($package['name']).'/allreleases.xml')) {
                $fullpackageinfo .= str_replace(
                    $this->getAllReleasesRESTProlog($package['name']), '
<a>
',
                file_get_contents($rdir.'/'.strtolower($package['name']).'/allreleases.xml'));

                $dirhandle = opendir($rdir.'/'.strtolower($package['name']));

                while (false !== ($entry = readdir($dirhandle))) {
                    if (strpos($entry, 'deps.') === 0) {
                        $version = str_replace(['deps.', '.txt'], ['', ''], $entry);
                        $fullpackageinfo .= '
<deps>
 <v>' . $version . '</v>
 <d>'.htmlspecialchars(utf8_encode(file_get_contents($rdir.'/'.strtolower($package['name']).'/'.$entry))).'</d>
</deps>
';
                    }
                }
            }
            $fullpackageinfo .= '</pi>
';
        }
        $fullpackageinfo .= '</f>';

        // list packages in a category
        $categoryDir = $cdir.'/'.urlencode($category);
        if (!file_exists($categoryDir)) {
            mkdir($categoryDir, 0777, true);
            @chmod($categoryDir, 0777);
        }

        file_put_contents($categoryDir.'/packagesinfo.xml', $fullpackageinfo);
        @chmod($categoryDir.'/packagesinfo.xml', 0666);
    }

    /**
     * Delete category info.
     */
    public function deleteCategory($category)
    {
        $cdir = $this->dir.'/c';

        if (!is_dir($cdir.'/'.urlencode($category))) {
            return;
        }

        // remove all category info
        $this->filesystem->delete($cdir.'/'.urlencode($category));
    }

    /**
     * Regenerate all packages info.
     */
    public function saveAllPackages()
    {
        $pdir = $this->dir.'/p';

        if (!file_exists($pdir)) {
            mkdir($pdir, 0777, true);
            @chmod($pdir, 0777);
        }

        $info = '<?xml version="1.0" encoding="UTF-8" ?>
<a xmlns="http://pear.php.net/dtd/rest.allpackages"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allpackages
    http://pear.php.net/dtd/rest.allpackages.xsd">
<c>' . PEAR_CHANNELNAME . '</c>
';
        foreach (Package::listAllNames() as $package)
        {
            $info .= ' <p>' . $package . '</p>
';
        }
        $info .= '</a>';
        file_put_contents($pdir.'/packages.xml', $info);
        @chmod($pdir.'/packages.xml', 0666);
    }

    /**
     * Return the XML prolog.
     */
    private function getPackageProlog()
    {
        return "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n" .
"<p xmlns=\"http://pear.php.net/dtd/rest.package\"" .
'    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"' .
"    xsi:schemaLocation=\"http://pear.php.net/dtd/rest.package" .
'    http://pear.php.net/dtd/rest.package.xsd">';
    }

    /**
     * Save package info.
     */
    public function savePackage($package)
    {
        $extra = '/rest/';
        $package = Package::info($package);

        $pdir = $this->dir.'/p';

        if (!file_exists($pdir)) {
            mkdir($pdir, 0777, true);
            @chmod($pdir, 0777);
        }

        $packageDir = $pdir.'/'.strtolower($package['name']);
        if (!file_exists($packageDir)) {
            mkdir($packageDir, 0777, true);
            @chmod($packageDir, 0777);
        }

        $catinfo = $this->dbh->getOne('SELECT c.name FROM packages, categories c WHERE
            c.id = ?', [$package['categoryid']], DB_FETCHMODE_ASSOC);
        if (isset($package['parent']) && $package['parent']) {
            $parent = '
 <pa xlink:href="' . $extra . 'p/' . $package['parent'] . '">' .
                $package['parent'] . '</pa>';
        } else {
            $parent = '';
        }

        if ($package['new_package']) {
            $dpackage = $package['new_package'];
            $deprecated = '
<dc>' . $package['new_channel'] . '</dc>
<dp> ' .
            $dpackage . '</dp>';
        } else {
            $deprecated = '';
        }

        $package['summary'] = htmlspecialchars($package['summary']);
        $package['description'] = htmlspecialchars($package['description']);
        $info = $this->getPackageProlog() . '
 <n>' . $package['name'] . '</n>
 <c>' . PEAR_CHANNELNAME . '</c>
 <ca xlink:href="' . $extra . 'c/' . htmlspecialchars(urlencode($catinfo)) . '">' .
        htmlspecialchars($catinfo) . '</ca>
 <l>' . $package['license'] . '</l>
 <s>' . $package['summary'] . '</s>
 <d>' . $package['description'] . '</d>
 <r xlink:href="' . $extra . 'r/' . strtolower($package['name']) . '"/>' . $parent . $deprecated . '
</p>';

        // package information
        file_put_contents($packageDir.'/info.xml', $info);
        @chmod($packageDir.'/info.xml', 0666);
    }

    /**
     * Remove package info.
     */
    public function deletePackage($package)
    {
        if (!$package) {
            // don't delete the entire package/release info
            return;
        }

        $pdir = $this->dir.'/p';
        $rdir = $this->dir.'/r';

        // remove all package/release info for this package
        $this->filesystem->delete($pdir.'/'.$package);
        $this->filesystem->delete($rdir.'/'.$package);
    }

    private function getAllReleasesRESTProlog($package)
    {
        return '<?xml version="1.0" encoding="UTF-8" ?>' . "\n" .
'<a xmlns="http://pear.php.net/dtd/rest.allreleases"' . "\n" .
'    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xlink="http://www.w3.org/1999/xlink" ' .
'    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases' . "\n" .
'    http://pear.php.net/dtd/rest.allreleases.xsd">' . "\n" .
' <p>' . $package . '</p>' . "\n" .
' <c>' . PEAR_CHANNELNAME . '</c>' . "\n";
    }

    /**
     * Regenerate all releases info.
     */
    public function saveAllReleases($package)
    {
        $extra = '/rest/';
        $pid = Package::info($package, 'id');
        $releases = $this->dbh->getAll('SELECT * FROM releases WHERE package = ? ORDER BY releasedate DESC', [$pid], DB_FETCHMODE_ASSOC);
        $rdir = $this->dir.'/r';

        if (!file_exists($rdir)) {
            mkdir($rdir, 0777, true);
            @chmod($rdir, 0777);
        }

        if (!$releases || !count($releases)) {
            // start from scratch, so that any pulled releases have their REST deleted
            $this->filesystem->delete($rdir.'/'.strtolower($package));

            return;
        }

        $info = $this->getAllReleasesRESTProlog($package);

        foreach ($releases as $release) {
            $packagexml = $this->dbh->getOne('SELECT packagexml FROM files WHERE package = ? AND
                `release` = ?', [$pid, $release['id']]);
            $extra = '';

            if (strpos($packagexml, ' version="2.0"')) {
                // little quick hack to determine package.xml version
                $pkg = new PEAR_PackageFile_Parser_v2;
                $config = PEAR_Config::singleton();
                $pkg->setConfig($config); // configuration is unused for this quick parse
                $pf = $pkg->parse($packagexml, '');

                if (PEAR::isError($pf)) {
                    return PEAR::raiseError(sprintf("Parsing the packagexml for release %s failed with error message: %s", $release['id'], $pf->getMessage()));
                }

                if ($compat = $pf->getCompatible()) {
                    if (!isset($compat[0])) {
                        $compat = [$compat];
                    }

                    foreach ($compat as $entry) {
                        $extra .= '<co><c>' . $entry['channel'] . '</c>' .
                            '<p>' . $entry['name'] . '</p>' .
                            '<min>' . $entry['min'] . '</min>' .
                            '<max>' . $entry['max'] . '</max>';

                        if (isset($entry['exclude'])) {
                            if (!is_array($entry['exclude'])) {
                                $entry['exclude'] = [$entry['exclude']];
                            }

                            foreach ($entry['exclude'] as $exclude) {
                                $extra .= '<x>' . $exclude . '</x>';
                            }
                        }

                        $extra .= '</co>
';
                    }
                }
            }

            if (!isset($latest)) {
                $latest = $release['version'];
            }

            if ($release['state'] == 'stable' && !isset($stable)) {
                $stable = $release['version'];
            }

            if ($release['state'] == 'beta' && !isset($beta)) {
                $beta = $release['version'];
            }

            if ($release['state'] == 'alpha' && !isset($alpha)) {
                $alpha = $release['version'];
            }

            $info .= ' <r><v>' . $release['version'] . '</v><s>' . $release['state'] . '</s>'
                 . $extra . '</r>
';
        }
        $info .= '</a>';

        $packageDir = $rdir.'/'.strtolower($package);
        if (!file_exists($packageDir)) {
            mkdir($packageDir, 0777, true);
            @chmod($packageDir, 0777);
        }

        file_put_contents($packageDir.'/allreleases.xml', $info);
        @chmod($packageDir.'/allreleases.xml', 0666);

        file_put_contents($packageDir.'/latest.txt', $latest);
        @chmod($packageDir.'/latest.txt', 0666);

        // remove .txt in case all releases of this stability were deleted
        @unlink($packageDir.'/stable.txt');
        @unlink($packageDir.'/beta.txt');
        @unlink($packageDir.'/alpha.txt');

        if (isset($stable)) {
            file_put_contents($packageDir.'/stable.txt', $stable);
            @chmod($packageDir.'/stable.txt', 0666);
        }

        if (isset($beta)) {
            file_put_contents($packageDir.'/beta.txt', $beta);
            @chmod($packageDir.'/beta.txt', 0666);
        }

        if (isset($alpha)) {
            file_put_contents($packageDir.'/alpha.txt', $alpha);
            @chmod($packageDir.'/alpha.txt', 0666);
        }
    }

    /**
     * Regenerate info by removing release information.
     */
    public function deleteRelease($package, $version)
    {
        $rdir = $this->dir.'/r';

        if (@is_dir($rdir.'/'.strtolower($package))) {
            @unlink($rdir.'/'.strtolower($package).'/'.$version.'.xml');
            @unlink($rdir.'/'.strtolower($package).'/package.'.$version.'.xml');
            @unlink($rdir.'/'.strtolower($package).'/deps.'.$version.'.txt');
        }
    }

    /**
     * Regenerate release info.
     */
    public function saveRelease($filepath, $packagexml, $pkgobj, $releasedby, $id)
    {
        $extra = '/rest/';
        $rdir = $this->dir.'/r';

        if (!file_exists($rdir)) {
            mkdir($rdir, 0777, true);
            @chmod($rdir, 0777);
        }

        $package = $pkgobj->getPackage();

        $packageDir = $rdir.'/'.strtolower($package);
        if (!file_exists($packageDir)) {
            mkdir($packageDir, 0777, true);
            @chmod($packageDir, 0777);
        }

        $releasedate = $this->dbh->getOne('SELECT releasedate FROM releases WHERE id = ?', [$id]);
        $info = '<?xml version="1.0" encoding="UTF-8" ?>
<r xmlns="http://pear.php.net/dtd/rest.release"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.release
    http://pear.php.net/dtd/rest.release.xsd">
 <p xlink:href="' . $extra . 'p/' . strtolower($package) . '">' . $package . '</p>
 <c>' . PEAR_CHANNELNAME . '</c>
 <v>' . $pkgobj->getVersion() . '</v>
 <st>' . $pkgobj->getState() . '</st>
 <l>' . $pkgobj->getLicense() . '</l>
 <m>' . $releasedby . '</m>
 <s>' . htmlspecialchars($pkgobj->getSummary()) . '</s>
 <d>' . htmlspecialchars($pkgobj->getDescription()) . '</d>
 <da>' . $releasedate . '</da>
 <n>' . htmlspecialchars($pkgobj->getNotes()) . '</n>
 <f>' . filesize($filepath) . '</f>
 <g>http://' . PEAR_CHANNELNAME . '/get/' . $package . '-' . $pkgobj->getVersion() . '</g>
 <x xlink:href="package.' . $pkgobj->getVersion() . '.xml"/>
</r>';
        file_put_contents($packageDir.'/'.$pkgobj->getVersion().'.xml', $info);
        @chmod($packageDir.'/'.$pkgobj->getVersion().'.xml', 0666);

        file_put_contents($packageDir.'/package.'.$pkgobj->getVersion().'.xml', $packagexml);
        @chmod($packageDir.'/package.'.$pkgobj->getVersion().'.xml', 0666);

        file_put_contents($packageDir.'/deps.'.$pkgobj->getVersion().'.txt', serialize($pkgobj->getDeps(true)));
        @chmod($packageDir.'/deps.'.$pkgobj->getVersion().'.txt', 0666);
    }

    public function deleteMaintainerREST($handle)
    {
        $mdir = $this->dir.'/m';

        if (is_dir($mdir.'/'.$handle)) {
            $this->filesystem->delete($mdir.'/'.$handle);
        }
    }

    /**
     * Regenerate package maintainer info.
     */
    public function savePackageMaintainer($package)
    {
        $pid = Package::info($package, 'id');
        $maintainers = $this->dbh->getAll('SELECT * FROM maintains WHERE package = ?', [$pid], DB_FETCHMODE_ASSOC);
        $extra = '/rest/';

        if (count($maintainers)) {
            $pdir = $this->dir.'/p';

            if (!file_exists($pdir)) {
                mkdir($pdir, 0777, true);
                @chmod($pdir, 0777);
            }

            $packageDir = $pdir.'/'.strtolower($package);
            if (!file_exists($packageDir)) {
                mkdir($packageDir, 0777, true);
                @chmod($packageDir, 0777);
            }

            $info = '<?xml version="1.0" encoding="UTF-8" ?>
<m xmlns="http://pear.php.net/dtd/rest.packagemaintainers"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.packagemaintainers
    http://pear.php.net/dtd/rest.packagemaintainers.xsd">
 <p>' . $package . '</p>
 <c>' . PEAR_CHANNELNAME . '</c>
';
            foreach ($maintainers as $maintainer) {
                $info .= ' <m><h>' . $maintainer['handle'] . '</h><a>' . $maintainer['active'] .
                    '</a></m>';
            }

            $info .= '</m>';
            file_put_contents($packageDir.'/maintainers.xml', $info);
            @chmod($packageDir.'/maintainers.xml', 0666);
        } else {
            @unlink($packageDir.'/maintainers.xml');
        }
    }

    /**
     * Regenerate maintainer info.
     */
    public function saveMaintainer($maintainer)
    {
        $maintainer = $this->dbh->getAll('SELECT * FROM users WHERE handle = ?',
            [$maintainer], DB_FETCHMODE_ASSOC);
        $maintainer = $maintainer[0];
        $extra = '/rest/';
        $mdir = $this->dir.'/m';

        if (!file_exists($mdir)) {
            mkdir($mdir, 0777, true);
            @chmod($mdir, 0777);
        }

        $handleDir = $mdir.'/'.$maintainer['handle'];
        if (!file_exists($handleDir)) {
            mkdir($handleDir, 0777, true);
            @chmod($handleDir, 0777);
        }

        if ($maintainer['homepage']) {
            $uri = ' <u>' . htmlspecialchars($maintainer['homepage']) . '</u>
';
        } else {
            $uri = '';
        }

        $maintainer['name'] = htmlspecialchars($maintainer['name']);
        $info = '<?xml version="1.0" encoding="UTF-8" ?>
<m xmlns="http://pear.php.net/dtd/rest.maintainer"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.maintainer
    http://pear.php.net/dtd/rest.maintainer.xsd">
 <h>' . $maintainer['handle'] . '</h>
 <n>' . $maintainer['name'] . '</n>
' . $uri . '</m>';

        // package information
        file_put_contents($handleDir.'/info.xml', $info);
        @chmod($handleDir.'/info.xml', 0666);
    }

    /**
     * Regenerate list of all maintainers.
     */
    public function saveAllMaintainers()
    {
        $maintainers = User::listAll();
        $info = '<?xml version="1.0" encoding="UTF-8" ?>
<m xmlns="http://pear.php.net/dtd/rest.allmaintainers"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allmaintainers
    http://pear.php.net/dtd/rest.allmaintainers.xsd">' . "\n";
        // package information

        $karma = new Karma($this->dbh);

        foreach ($maintainers as $maintainer) {
            if (!$karma->has($maintainer['handle'], 'pear.dev')) {
                continue;
            }
            $info .= ' <h xlink:href="/rest/m/' . $maintainer['handle'] . '">' .
                $maintainer['handle'] . '</h>' . "\n";
        }
        $info .= '</m>';

        $mdir = $this->dir.'/m';

        if (!file_exists($mdir)) {
            mkdir($mdir, 0777, true);
            @chmod($mdir, 0777);
        }

        file_put_contents($mdir.'/allmaintainers.xml', $info);
        @chmod($mdir.'/allmaintainers.xml', 0666);
    }
}
