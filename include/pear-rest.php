<?php
class pear_rest
{
    var $_restdir;
    public function __construct($base)
    {
        $this->_restdir = $base;
    }

    function saveAllCategoriesREST()
    {
        require_once 'System.php';

        $extra = '/rest/';
        $cdir = $this->_restdir . DIRECTORY_SEPARATOR . 'c';
        if (!is_dir($cdir)) {
            System::mkdir(['-p', $cdir]);
            @chmod($cdir, 0777);
        }

        $categories = category::listAll();
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
        file_put_contents($cdir . DIRECTORY_SEPARATOR . 'categories.xml', $info);
        @chmod($cdir . DIRECTORY_SEPARATOR . 'categories.xml', 0666);
    }

    function saveCategoryREST($category)
    {
        require_once 'System.php';
        global $dbh;
        $extra = '/rest/';
        $cdir = $this->_restdir . DIRECTORY_SEPARATOR . 'c';
        if (!is_dir($cdir)) {
            System::mkdir(['-p', $cdir]);
            @chmod($cdir, 0777);
        }
        $category = $dbh->getAll('SELECT * FROM categories WHERE name = ?', [$category],
            DB_FETCHMODE_ASSOC);
        $category = $category[0];
        if (!is_dir($cdir . DIRECTORY_SEPARATOR . urlencode($category['name']))) {
            System::mkdir(['-p', $cdir . DIRECTORY_SEPARATOR . urlencode($category['name'])]);
            @chmod($cdir . DIRECTORY_SEPARATOR . urlencode($category['name']), 0777);
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
 <d>' . (version_compare(phpversion(), '5.0.0', 'lt') ?
            utf8_encode($category['description']) :
            $category['description']) . '</d>
</c>';
        // category info
        file_put_contents($cdir . DIRECTORY_SEPARATOR . urlencode($category['name']) .
            DIRECTORY_SEPARATOR . 'info.xml', $info);
        @chmod($cdir . DIRECTORY_SEPARATOR . urlencode($category['name']) .
            DIRECTORY_SEPARATOR . 'info.xml', 0666);
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

        $sth = $dbh->getAll($query, [$category['name']], DB_FETCHMODE_ASSOC);
        foreach ($sth as $package) {
            $list .= ' <p xlink:href="' . $extra . 'p/' . strtolower($package['name']) . '">' .
                $package['name'] . '</p>
';
        }
        $list .= '</l>';
        // list packages in a category
        file_put_contents($cdir . DIRECTORY_SEPARATOR . urlencode($category['name']) .
            DIRECTORY_SEPARATOR . 'packages.xml', $list);
        @chmod($cdir . DIRECTORY_SEPARATOR . urlencode($category['name']) .
            DIRECTORY_SEPARATOR . 'packages.xml', 0666);
    }

    function savePackagesCategoryREST($category)
    {
        $cdir = $this->_restdir . DIRECTORY_SEPARATOR . 'c';
        if (!is_dir($cdir)) {
            return;
        }
        $pdir = $this->_restdir . DIRECTORY_SEPARATOR . 'p';
        $rdir = $this->_restdir . DIRECTORY_SEPARATOR . 'r';
        $packages = category::listPackages($category);
        $fullpackageinfo = '<?xml version="1.0" encoding="UTF-8" ?>
<f xmlns="http://pear.php.net/dtd/rest.categorypackageinfo"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.categorypackageinfo
    http://pear.php.net/dtd/rest.categorypackageinfo.xsd">
';
        clearstatcache();
        foreach ($packages as $package) {
            if (!file_exists($pdir . DIRECTORY_SEPARATOR . strtolower($package['name']) .
                    DIRECTORY_SEPARATOR . 'info.xml')) {
                continue;
            }
            $fullpackageinfo .= '<pi>
';
            $contents = file_get_contents($pdir . DIRECTORY_SEPARATOR . strtolower($package['name']) .
                    DIRECTORY_SEPARATOR . 'info.xml');
            $fullpackageinfo .= '<p>' . substr($contents, strpos($contents, '<n>'));
            if (file_exists($rdir . DIRECTORY_SEPARATOR . strtolower($package['name']) .
                    DIRECTORY_SEPARATOR . 'allreleases.xml')) {
                $fullpackageinfo .= str_replace(
                    $this->_getAllReleasesRESTProlog($package['name']), '
<a>
',
                    file_get_contents($rdir . DIRECTORY_SEPARATOR .
                        strtolower($package['name']) . DIRECTORY_SEPARATOR .
                        'allreleases.xml'));
                $dirhandle = opendir($rdir . DIRECTORY_SEPARATOR .
                    strtolower($package['name']));
                while (false !== ($entry = readdir($dirhandle))) {
                    if (strpos($entry, 'deps.') === 0) {
                        $version = str_replace(['deps.', '.txt'], ['', ''], $entry);
                        $fullpackageinfo .= '
<deps>
 <v>' . $version . '</v>
 <d>' . htmlspecialchars(utf8_encode(file_get_contents($rdir . DIRECTORY_SEPARATOR .
                        strtolower($package['name']) . DIRECTORY_SEPARATOR .
                        $entry))) . '</d>
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
        if (!is_dir($cdir . DIRECTORY_SEPARATOR . urlencode($category))) {
            mkdir($cdir . DIRECTORY_SEPARATOR . urlencode($category));
        }
        file_put_contents($cdir . DIRECTORY_SEPARATOR . urlencode($category) .
            DIRECTORY_SEPARATOR . 'packagesinfo.xml', $fullpackageinfo);
        @chmod($cdir . DIRECTORY_SEPARATOR . urlencode($category) .
            DIRECTORY_SEPARATOR . 'packagesinfo.xml', 0666);
    }

    function deleteCategoryREST($category)
    {
        require_once 'System.php';
        $cdir = $this->_restdir . DIRECTORY_SEPARATOR . 'c';
        if (!is_dir($cdir . DIRECTORY_SEPARATOR . urlencode($category))) {
            return;
        }
        // remove all category info
        System::rm(['-r', $cdir . DIRECTORY_SEPARATOR . urlencode($category)]);
    }

    function saveAllPackagesREST()
    {
        require_once 'System.php';
        $pdir = $this->_restdir . DIRECTORY_SEPARATOR . 'p';
        if (!is_dir($pdir)) {
            System::mkdir(['-p', $pdir]);
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
        foreach (package::listAllNames() as $package)
        {
            $info .= ' <p>' . $package . '</p>
';
        }
        $info .= '</a>';
        file_put_contents($pdir . DIRECTORY_SEPARATOR . 'packages.xml', $info);
        @chmod($pdir . DIRECTORY_SEPARATOR . 'packages.xml', 0666);
    }

    function _getPackageRESTProlog()
    {
        return "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n" .
"<p xmlns=\"http://pear.php.net/dtd/rest.package\"" .
'    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"' .
"    xsi:schemaLocation=\"http://pear.php.net/dtd/rest.package" .
'    http://pear.php.net/dtd/rest.package.xsd">';
    }

    function savePackageREST($package)
    {
        require_once 'System.php';
        global $dbh;
        $extra = '/rest/';
        $package = package::info($package);

        $pdir = $this->_restdir . DIRECTORY_SEPARATOR . 'p';
        if (!is_dir($pdir)) {
            System::mkdir(['-p', $pdir]);
            @chmod($pdir, 0777);
        }
        if (!is_dir($pdir . DIRECTORY_SEPARATOR . strtolower($package['name']))) {
            System::mkdir(['-p', $pdir . DIRECTORY_SEPARATOR .
                strtolower($package['name'])]);
            @chmod($pdir . DIRECTORY_SEPARATOR . strtolower($package['name']), 0777);
        }
        $catinfo = $dbh->getOne('SELECT c.name FROM packages, categories c WHERE 
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
        $info = $this->_getPackageRESTProlog() . '
 <n>' . $package['name'] . '</n>
 <c>' . PEAR_CHANNELNAME . '</c>
 <ca xlink:href="' . $extra . 'c/' . htmlspecialchars(urlencode($catinfo)) . '">' .
        htmlspecialchars($catinfo) . '</ca>
 <l>' . $package['license'] . '</l>
 <s>' . (version_compare(phpversion(), '5.0.0', 'lt') ?
            utf8_encode($package['summary']) :
            $package['summary']) . '</s>
 <d>' . (version_compare(phpversion(), '5.0.0', 'lt') ?
            utf8_encode($package['description']) :
            $package['description']) . '</d>
 <r xlink:href="' . $extra . 'r/' . strtolower($package['name']) . '"/>' . $parent . $deprecated . '
</p>';
        // package information
        file_put_contents($pdir . DIRECTORY_SEPARATOR . strtolower($package['name']) .
            DIRECTORY_SEPARATOR . 'info.xml', $info);
        @chmod($pdir . DIRECTORY_SEPARATOR . strtolower($package['name']) .
            DIRECTORY_SEPARATOR . 'info.xml', 0666);
    }

    function deletePackageREST($package)
    {
        if (!$package) {
            // don't delete the entire package/release info
            return;
        }
        require_once 'System.php';
        $pdir = $this->_restdir . DIRECTORY_SEPARATOR . 'p';
        $rdir = $this->_restdir . DIRECTORY_SEPARATOR . 'r';
        // remove all package/release info for this package
        System::rm(['-r', $pdir . DIRECTORY_SEPARATOR . $package]);
        System::rm(['-r', $rdir . DIRECTORY_SEPARATOR . $package]);
    }

    function _getAllReleasesRESTProlog($package)
    {
        return '<?xml version="1.0" encoding="UTF-8" ?>' . "\n" .
'<a xmlns="http://pear.php.net/dtd/rest.allreleases"' . "\n" .
'    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xlink="http://www.w3.org/1999/xlink" ' .
'    xsi:schemaLocation="http://pear.php.net/dtd/rest.allreleases' . "\n" .
'    http://pear.php.net/dtd/rest.allreleases.xsd">' . "\n" .
' <p>' . $package . '</p>' . "\n" .
' <c>' . PEAR_CHANNELNAME . '</c>' . "\n";
    }

    function saveAllReleasesREST($package)
    {
        require_once 'System.php';
        require_once 'PEAR/PackageFile/Parser/v2.php';
        require_once 'PEAR/Config.php';
        global $dbh;
        $extra = '/rest/';
        $pid = package::info($package, 'id');
        $releases = $dbh->getAll('SELECT * FROM releases WHERE package = ? ORDER BY releasedate DESC',
            [$pid], DB_FETCHMODE_ASSOC);
        $rdir = $this->_restdir . DIRECTORY_SEPARATOR . 'r';
        if (!is_dir($rdir)) {
            System::mkdir(['-p', $rdir]);
            @chmod($rdir, 0777);
        }
        if (!$releases || !count($releases)) {
            // start from scratch, so that any pulled releases have their REST deleted
            System::rm(['-r', $rdir . DIRECTORY_SEPARATOR . strtolower($package)]);
            return;
        }
        $info = $this->_getAllReleasesRESTProlog($package);
        foreach ($releases as $release) {
            $packagexml = $dbh->getOne('SELECT packagexml FROM files WHERE package = ? AND
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
        if (!is_dir($rdir . DIRECTORY_SEPARATOR . strtolower($package))) {
            System::mkdir(['-p', $rdir . DIRECTORY_SEPARATOR . strtolower($package)]);
            @chmod($rdir . DIRECTORY_SEPARATOR . strtolower($package), 0777);
        }
        file_put_contents($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
            DIRECTORY_SEPARATOR . 'allreleases.xml', $info);
        @chmod($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
            DIRECTORY_SEPARATOR . 'allreleases.xml', 0666);

        file_put_contents($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
            DIRECTORY_SEPARATOR . 'latest.txt', $latest);
        @chmod($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
            DIRECTORY_SEPARATOR . 'latest.txt', 0666);
        // remove .txt in case all releases of this stability were deleted
        @unlink($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
            DIRECTORY_SEPARATOR . 'stable.txt');
        @unlink($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
            DIRECTORY_SEPARATOR . 'beta.txt');
        @unlink($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
            DIRECTORY_SEPARATOR . 'alpha.txt');
        if (isset($stable)) {
            file_put_contents($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
                DIRECTORY_SEPARATOR . 'stable.txt', $stable);
            @chmod($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
                DIRECTORY_SEPARATOR . 'stable.txt', 0666);
        }
        if (isset($beta)) {
            file_put_contents($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
                DIRECTORY_SEPARATOR . 'beta.txt', $beta);
            @chmod($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
                DIRECTORY_SEPARATOR . 'beta.txt', 0666);
        }
        if (isset($alpha)) {
            file_put_contents($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
                DIRECTORY_SEPARATOR . 'alpha.txt', $alpha);
            @chmod($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
                DIRECTORY_SEPARATOR . 'alpha.txt', 0666);
        }
    }

    function deleteReleaseREST($package, $version)
    {
        require_once 'System.php';
        $rdir = $this->_restdir . DIRECTORY_SEPARATOR . 'r';
        if (@is_dir($rdir . DIRECTORY_SEPARATOR . strtolower($package))) {
            @unlink($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
                DIRECTORY_SEPARATOR . $version . '.xml');
            @unlink($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
                DIRECTORY_SEPARATOR . 'package.' . $version . '.xml');
            @unlink($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
                DIRECTORY_SEPARATOR . 'deps.' . $version . '.txt');
        }
    }

    function saveReleaseREST($filepath, $packagexml, $pkgobj, $releasedby, $id)
    {
        require_once 'System.php';
        global $dbh;
        $extra = '/rest/';
        $rdir = $this->_restdir . DIRECTORY_SEPARATOR . 'r';
        if (!is_dir($rdir)) {
            System::mkdir(['-p', $rdir]);
            @chmod($rdir, 0777);
        }

        $package = $pkgobj->getPackage();
        if (!is_dir($rdir . DIRECTORY_SEPARATOR . strtolower($package))) {
            System::mkdir(['-p', $rdir . DIRECTORY_SEPARATOR . strtolower($package)]);
            @chmod($rdir . DIRECTORY_SEPARATOR . strtolower($package), 0777);
        }

        $releasedate = $dbh->getOne('SELECT releasedate FROM releases WHERE id = ?',
            [$id]);
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
 <s>' . (version_compare(phpversion(), '5.0.0', 'lt') ?
            utf8_encode(htmlspecialchars($pkgobj->getSummary())) :
            htmlspecialchars($pkgobj->getSummary())) . '</s>
 <d>' . (version_compare(phpversion(), '5.0.0', 'lt') ?
            utf8_encode(htmlspecialchars($pkgobj->getDescription())) :
            htmlspecialchars($pkgobj->getDescription())) . '</d>
 <da>' . $releasedate . '</da>
 <n>' . (version_compare(phpversion(), '5.0.0', 'lt') ?
            utf8_encode(htmlspecialchars($pkgobj->getNotes())) :
            htmlspecialchars($pkgobj->getNotes())) . '</n>
 <f>' . filesize($filepath) . '</f>
 <g>http://' . PEAR_CHANNELNAME . '/get/' . $package . '-' . $pkgobj->getVersion() . '</g>
 <x xlink:href="package.' . $pkgobj->getVersion() . '.xml"/>
</r>';
        file_put_contents($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
            DIRECTORY_SEPARATOR . $pkgobj->getVersion() . '.xml', $info);
        @chmod($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
            DIRECTORY_SEPARATOR . $pkgobj->getVersion() . '.xml', 0666);
        file_put_contents($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
            DIRECTORY_SEPARATOR . 'package.' .
            $pkgobj->getVersion() . '.xml', $packagexml);
        @chmod($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
            DIRECTORY_SEPARATOR . 'package.' . $pkgobj->getVersion() . '.xml', 0666);
        file_put_contents($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
            DIRECTORY_SEPARATOR . 'deps.' . $pkgobj->getVersion() . '.txt', serialize($pkgobj->getDeps(true)));
        @chmod($rdir . DIRECTORY_SEPARATOR . strtolower($package) .
            DIRECTORY_SEPARATOR . 'deps.' . $pkgobj->getVersion() . '.txt', 0666);
    }

    function deleteMaintainerREST($handle)
    {
        require_once 'System.php';
        $mdir = $this->_restdir . DIRECTORY_SEPARATOR . 'm';
        if (is_dir($mdir . DIRECTORY_SEPARATOR . $handle)) {
            System::rm(['-r', $mdir . DIRECTORY_SEPARATOR . $handle]);
        }
    }

    function savePackageMaintainerREST($package)
    {
        require_once 'System.php';
        global $dbh;
        $pid = package::info($package, 'id');
        $maintainers = $dbh->getAll('SELECT * FROM maintains WHERE package = ?', [$pid],
            DB_FETCHMODE_ASSOC);
        $extra = '/rest/';
        if (count($maintainers)) {
            $pdir = $this->_restdir . DIRECTORY_SEPARATOR . 'p';
            if (!is_dir($pdir)) {
                System::mkdir(['-p', $pdir]);
                @chmod($pdir, 0777);
            }
            if (!is_dir($pdir . DIRECTORY_SEPARATOR . strtolower($package))) {
                System::mkdir(['-p', $pdir . DIRECTORY_SEPARATOR . strtolower($package)]);
                @chmod($pdir . DIRECTORY_SEPARATOR . strtolower($package), 0777);
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
            file_put_contents($pdir . DIRECTORY_SEPARATOR . strtolower($package) .
                DIRECTORY_SEPARATOR . 'maintainers.xml', $info);
            @chmod($pdir . DIRECTORY_SEPARATOR . strtolower($package) .
                DIRECTORY_SEPARATOR . 'maintainers.xml', 0666);
        } else {
            @unlink($pdir . DIRECTORY_SEPARATOR . strtolower($package) .
                DIRECTORY_SEPARATOR . 'maintainers.xml');
        }
    }

    function saveMaintainerREST($maintainer)
    {
        require_once 'System.php';
        global $dbh;
        $maintainer = $dbh->getAll('SELECT * FROM users WHERE handle = ?',
            [$maintainer], DB_FETCHMODE_ASSOC);
        $maintainer = $maintainer[0];
        $extra = '/rest/';
        $mdir = $this->_restdir . DIRECTORY_SEPARATOR . 'm';
        if (!is_dir($mdir)) {
            System::mkdir(['-p', $mdir]);
            @chmod($mdir, 0777);
        }
        if (!is_dir($mdir . DIRECTORY_SEPARATOR . $maintainer['handle'])) {
            System::mkdir(['-p', $mdir . DIRECTORY_SEPARATOR . $maintainer['handle']]);
            @chmod($mdir . DIRECTORY_SEPARATOR . $maintainer['handle'], 0777);
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
 <n>' . (version_compare(phpversion(), '5.0.0', 'lt') ?
            utf8_encode($maintainer['name']) :
            $maintainer['name']) . '</n>
' . $uri . '</m>';
        // package information
        file_put_contents($mdir . DIRECTORY_SEPARATOR . $maintainer['handle'] .
            DIRECTORY_SEPARATOR . 'info.xml', $info);
        @chmod($mdir . DIRECTORY_SEPARATOR . $maintainer['handle'] .
            DIRECTORY_SEPARATOR . 'info.xml', 0666);
    }

    function saveAllMaintainersREST()
    {
        $maintainers = user::listAll();
        $info = '<?xml version="1.0" encoding="UTF-8" ?>
<m xmlns="http://pear.php.net/dtd/rest.allmaintainers"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xmlns:xlink="http://www.w3.org/1999/xlink"
    xsi:schemaLocation="http://pear.php.net/dtd/rest.allmaintainers
    http://pear.php.net/dtd/rest.allmaintainers.xsd">' . "\n";
        // package information
        require_once 'Damblan/Karma.php';
        $karma = new Damblan_Karma($GLOBALS['dbh']);
        foreach ($maintainers as $maintainer) {
            if (!$karma->has($maintainer['handle'], 'pear.dev')) {
                continue;
            }
            $info .= ' <h xlink:href="/rest/m/' . $maintainer['handle'] . '">' .
                $maintainer['handle'] . '</h>' . "\n";
        }
        $info .= '</m>';
        $mdir = $this->_restdir . DIRECTORY_SEPARATOR . 'm';
        if (!is_dir($mdir)) {
            System::mkdir(['-p', $mdir]);
            @chmod($mdir, 0777);
        }
        file_put_contents($mdir . DIRECTORY_SEPARATOR . 'allmaintainers.xml', $info);
        @chmod($mdir . DIRECTORY_SEPARATOR . 'allmaintainers.xml', 0666);
    }
}
?>
