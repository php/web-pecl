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
  | Authors: Anatol Belski <ab@php.net>                                  |
  +----------------------------------------------------------------------+
*/

define("PECL_DLL_URL_CACHE_DB", PEAR_TMPDIR . DIRECTORY_SEPARATOR . "pecl_dll_url.cache");
define("PECL_DLL_URL_CACHE_LAST_RESET", PEAR_TMPDIR . DIRECTORY_SEPARATOR . "pecl_dll_last_reset");
define("PECL_DLL_URL_CACHE_DB_RESET_LOCK", PEAR_TMPDIR . DIRECTORY_SEPARATOR . "pecl_dll_url_cache_reset.lock");

/**
 * Class to handle package DLL builds
 */
class PackageDll
{
	protected static $build_gap = 7200; /* 2 hours */

	protected static $reset_period = 3600; /* 1 hour */

	protected static $cache_db = PECL_DLL_URL_CACHE_DB;

	protected static $last_reset_file = PECL_DLL_URL_CACHE_LAST_RESET;
	protected static $cache_reset_lock = PECL_DLL_URL_CACHE_DB_RESET_LOCK;

	/* NOTE when edit here, don't forget to remove the cache file */
	protected static $zip_name_parts = [
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

	public static function resetDllDownloadCache()
	{
		clearstatcache();
		if (file_exists(self::$cache_reset_lock)) {
			/* Reset is started by some other process in that small time gap.
				That's still not full atomic, but reduces the risks significantly.  */
			/* yeah, go to ... */
			return false;
		}

		touch(self::$cache_reset_lock);

		if (!file_exists(self::$last_reset_file)) {
			touch(self::$last_reset_file);
		}
		file_put_contents(self::$last_reset_file, time(), LOCK_EX);
		file_put_contents(self::$cache_db, serialize([]), LOCK_EX);

		unlink(self::$cache_reset_lock);

		return true;
	}

	public static function getDllDownloadUrls($name, $version, $date, $cache = true)
	{
		$db = [];
		$ret = NULL;
		$cached_found = false;
		$do_cache = false;

		if (!self::buildGapOver($date)) {
			return NULL;
		}

		/* If cache reset lock exists, some reset is running right now. Deliver
			the live results then and don't cache. */
		$cache = $cache && !file_exists(self::$cache_reset_lock);

		do {
			if ($cache) {
				if (self::isResetOverdue()) {
					$cache = self::resetDllDownloadCache();
				}
			}

			if (file_exists(self::$cache_db)) {
				$db = (array)unserialize(file_get_contents(self::$cache_db));
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

		/* not cached yet */
		if (!$ret && !$cached_found) {
			$do_cache = true;
			$ret = self::fetchDllDownloadUrls($name, $version);
		}

		if ($cache && $do_cache) {
			self::cacheDllDownloadInfo($name, $version, $ret);
		}

		return $ret;
	}

	public static function updateDllDownloadCache($name, $version)
	{
		$db = [];

		if (file_exists(self::$cache_db)) {
			$db = (array)unserialize(file_get_contents(self::$cache_db));
		}

		foreach($db as $ext => $data) {
			if ($ext != $name) {
				continue;
			}

			if (is_array($data) && array_key_exists($version, $data)) {
				/* found cached, nothing to do */
				return true;
			}
		}

		$pkg = self::fetchDllDownloadUrls($name, $version);

		return self::cacheDllDownloadInfo($name, $version, $pkg);
	}

	public static function cacheDllDownloadInfo($name, $version, $data)
	{
		$db = [];

		if (file_exists(self::$cache_db)) {
			$db = (array)unserialize(file_get_contents(self::$cache_db));
		}

		if (!isset($db[$name])) {
			$db[$name] = [];
		}

		$db[$name][$version] = $data;

		return false !== file_put_contents(self::$cache_db, serialize($db), LOCK_EX);
	}

	/* need always both ts/nts for each branch */
	public static function getZipFileList($name, $version)
	{
		$ret = [];

		foreach (self::$zip_name_parts as $branch => $data) {
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

	public static function fetchDllDownloadUrls($name, $version)
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

		foreach (self::getZipFileList($name, $version) as $branch => $data) {
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

	public static function buildGapOver($date)
	{
		    /* Between the package release and DLL build can be the gap of
			   30 minutes (in the best case). Lets give it 2h so we don't
			   cache empty result too early. */

			$dt = date_parse($date);
			$rel_ts = mktime($dt['hour'], $dt['minute'], $dt['second'], $dt['month'], $dt['day'], $dt['year']);

		    return time() >= $rel_ts+self::$build_gap;
	}

	public static function makeNiceLinkNameFromZipName($zip_name)
	{
		/* name looks like php_taint-1.1.0-5.4-nts-vc9-x86.zip*/
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

	public static function isResetOverdue()
	{
		if (!file_exists(self::$last_reset_file)) {
			file_put_contents(self::$last_reset_file, 0, LOCK_EX);
		}

		$ts = (int)file_get_contents(self::$last_reset_file);

		if (time() - $ts > self::$reset_period) {
			return true;
		}

		return false;
	}
}
