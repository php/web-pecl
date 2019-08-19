<?php

/*
  +----------------------------------------------------------------------+
  | The PECL website                                                     |
  +----------------------------------------------------------------------+
  | Copyright (c) 1999-2019 The PHP Group                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | https://php.net/license/3_01.txt                                     |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Authors: Anatol Belski <ab@php.net>                                  |
  +----------------------------------------------------------------------+
*/

namespace App;

/**
 * Class to handle package DLL builds
 */
class PackageDll
{
    /**
     * Temporary directory for storing cache files.
     */
    private $tmpDir;

    /**
     * Build gap defaults to 2 hours.
     */
    private $build_gap = 7200;

    /**
     * Reset period defaults to 1 hour.
     */
    private $reset_period = 3600;

    private $cacheDbFile;
    private $lastResetFile;
    private $cacheResetLockFile;

    /**
     * NOTE when edit here, don't forget to remove the cache file
     */
    private $zip_name_parts = [
        '7.4' => [
            ['crt' => 'vc15', 'arch' => 'x64'],
            ['crt' => 'vc15', 'arch' => 'x86'],
        ],
        '7.3' => [
            ['crt' => 'vc15', 'arch' => 'x64'],
            ['crt' => 'vc15', 'arch' => 'x86'],
        ],
        '7.2' => [
            ['crt' => 'vc15', 'arch' => 'x64'],
            ['crt' => 'vc15', 'arch' => 'x86'],
        ],
        '7.1' => [
            ['crt' => 'vc14', 'arch' => 'x64'],
            ['crt' => 'vc14', 'arch' => 'x86'],
        ],
        '7.0' => [
            ['crt' => 'vc14', 'arch' => 'x64'],
            ['crt' => 'vc14', 'arch' => 'x86'],
        ],
        '5.6' => [
            ['crt' => 'vc11', 'arch' => 'x64'],
            ['crt' => 'vc11', 'arch' => 'x86'],
        ],
        '5.5' => [
            ['crt' => 'vc11', 'arch' => 'x64'],
            ['crt' => 'vc11', 'arch' => 'x86'],
        ],
        '5.4' => [
            ['crt' => 'vc9', 'arch' => 'x86'],
        ],
        '5.3' => [
            ['crt' => 'vc9', 'arch' => 'x86'],
        ],
    ];

    /**
     * Class constructor.
     */
    public function __construct($tmpDir)
    {
        $this->tmpDir = $tmpDir;
        $this->cacheDbFile = $this->tmpDir.'/pecl_dll_url.cache';
        $this->lastResetFile = $this->tmpDir.'/pecl_dll_last_reset';
        $this->cacheResetLockFile = $this->tmpDir.'/pecl_dll_url_cache_reset.lock';
    }

    public function resetDllDownloadCache()
    {
        clearstatcache();
        if (file_exists($this->cacheResetLockFile)) {
            // Reset is started by some other process in that small time gap.
            // That's still not full atomic, but reduces the risks significantly.
            return false;
        }

        touch($this->cacheResetLockFile);

        if (!file_exists($this->lastResetFile)) {
            touch($this->lastResetFile);
        }
        file_put_contents($this->lastResetFile, time(), LOCK_EX);
        file_put_contents($this->cacheDbFile, serialize([]), LOCK_EX);

        unlink($this->cacheResetLockFile);

        return true;
    }

    public function getDllDownloadUrls($name, $version, $date, $cache = true)
    {
        $db = [];
        $ret = NULL;
        $cached_found = false;
        $do_cache = false;

        if (!$this->buildGapOver($date)) {
            return NULL;
        }

        // If cache reset lock exists, some reset is running right now. Deliver
        // the live results then and don't cache.
        $cache = $cache && !file_exists($this->cacheResetLockFile);

        do {
            if ($cache) {
                if ($this->isResetOverdue()) {
                    $cache = $this->resetDllDownloadCache();
                }
            }

            if (file_exists($this->cacheDbFile)) {
                $db = (array)unserialize(file_get_contents($this->cacheDbFile));
            }

            foreach($db as $ext => $data) {
                if ($ext != $name) {
                    continue;
                }

                if (is_array($data) && array_key_exists($version, $data)) {
                    $ret = $data[$version];
                    $cached_found = true;
                    break;
                }
            }
        } while (0);

        // Not cached yet.
        if (!$ret && !$cached_found) {
            $do_cache = true;
            $ret = $this->fetchDllDownloadUrls($name, $version);
        }

        if ($cache && $do_cache) {
            $this->cacheDllDownloadInfo($name, $version, $ret);
        }

        return $ret;
    }

    public function updateDllDownloadCache($name, $version)
    {
        $db = [];

        if (file_exists($this->cacheDbFile)) {
            $db = (array)unserialize(file_get_contents($this->cacheDbFile));
        }

        foreach($db as $ext => $data) {
            if ($ext != $name) {
                continue;
            }

            if (is_array($data) && array_key_exists($version, $data)) {
                // Found cached, nothing to do.
                return true;
            }
        }

        $pkg = $this->fetchDllDownloadUrls($name, $version);

        return $this->cacheDllDownloadInfo($name, $version, $pkg);
    }

    private function cacheDllDownloadInfo($name, $version, $data)
    {
        $db = [];

        if (file_exists($this->cacheDbFile)) {
            $db = (array)unserialize(file_get_contents($this->cacheDbFile));
        }

        if (!isset($db[$name])) {
            $db[$name] = [];
        }

        $db[$name][$version] = $data;

        return false !== file_put_contents($this->cacheDbFile, serialize($db), LOCK_EX);
    }

    /**
     * Need always both ts/nts for each branch.
     */
    private function getZipFileList($name, $version)
    {
        $ret = [];

        foreach ($this->zip_name_parts as $branch => $data) {
            foreach ($data as $set) {
                $pref = "php_" . $name . "-" . $version . "-" . $branch;
                $suf = $set["crt"] . "-" . $set["arch"] . ".zip";

                if (!isset($ret[$branch])) {
                    $ret[$branch] = [];
                }
                if (!isset($ret[$branch][$set["arch"]])) {
                    $ret[$branch][$set["arch"]] = [];
                }
                $ret[$branch][$set["arch"]][] = strtolower($pref . "-nts-" . $suf);
                $ret[$branch][$set["arch"]][] = strtolower($pref . "-ts-" . $suf);
            }
        }

        return $ret;
    }

    private function fetchDllDownloadUrls($name, $version)
    {
        $host = 'windows.php.net';
        $port = 80;
        $uri = "/downloads/pecl/releases/" . strtolower($name) . "/" . $version;
        $ret = [];

        $ctx = stream_context_create(["http" => ["header" => "User-Agent: WebPecl/1.0"]]);
        $r = file_get_contents("https://$host$uri/", false, $ctx);
        if (false === $r) {
            return NULL;
        }

        foreach ($this->getZipFileList($name, $version) as $branch => $data) {
            foreach ($data as $arch => $zips) {
                $branch_ok = true;

                foreach ($zips as $zip) {
                    $branch_ok = $branch_ok && strpos(strtolower($r), $zip);
                }

                if ($branch_ok) {
                    $tmp = [];
                    foreach ($zips as $zip) {
                        $tmp[] = "https://$host$uri/$zip";
                    }

                    if (!isset($ret[$branch])) {
                        $ret[$branch] = [];
                    }
                    $ret[$branch] = array_merge($ret[$branch], $tmp);
                }
            }
        }

        return $ret;
    }

    /**
     * Between the package release and DLL build can be the gap of 30 minutes
     * (in the best case). Lets give it 2h so we don't cache empty result too
     * early.
     */
    private function buildGapOver($date)
    {
        $dt = date_parse($date);
        $rel_ts = mktime($dt['hour'], $dt['minute'], $dt['second'], $dt['month'], $dt['day'], $dt['year']);

        return time() >= $rel_ts + $this->build_gap;
    }

    public function makeNiceLinkNameFromZipName($zip_name)
    {
        // Name looks like php_taint-1.1.0-5.4-nts-vc9-x86.zip
        if (!preg_match(",php_([^-]+)-([a-z0-9\.]+)-([0-9\.]+)-(ts|nts)-(vc\d+)-(x86|x64)\.zip,", $zip_name, $part)) {
            return $zip_name;
        }

        $name = $part[1];
        $version = $part[2];
        $branch = $part[3];
        $zts = $part[4];
        $crt = $part[5];
        $arch = $part[6];

        $zts_str = 'ts' == $zts ? "Thread Safe" : "Non Thread Safe";

        return "$branch $zts_str (" . strtoupper($zts) . ") $arch";
    }

    public function isResetOverdue()
    {
        if (!file_exists($this->lastResetFile)) {
            file_put_contents($this->lastResetFile, 0, LOCK_EX);
        }

        $ts = (int)file_get_contents($this->lastResetFile);

        if (time() - $ts > $this->reset_period) {
            return true;
        }

        return false;
    }
}
