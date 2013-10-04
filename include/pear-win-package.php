<?php
/*
   +----------------------------------------------------------------------+
   | PEAR Web site version 1.0                                            |
   +----------------------------------------------------------------------+
   | Copyright (c) 2001-2005 The PHP Group                                |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.02 of the PHP license,      |
   | that is bundled with this package in the file LICENSE, and is        |
   | available at through the world-wide-web at                           |
   | http://www.php.net/license/2_02.txt.                                 |
   | If you did not receive a copy of the PHP license and are unable to   |
   | obtain it through the world-wide-web, please send a note to          |
   | license@php.net so we can mail you a copy immediately.               |
   +----------------------------------------------------------------------+
   | Authors: Anatol Belski <ab@php.net>                                  |
   +----------------------------------------------------------------------+
   $Id$
*/

define("PECL_DLL_URL_CACHE_DB", PEAR_TMPDIR . DIRECTORY_SEPARATOR . "pecl_dll_url.cache");

/**
 * Class to handle package DLL builds
 *
 * @class   package_dll
 * @package pearweb
 */
class package_dll
{
	protected static $cache_db = PECL_DLL_URL_CACHE_DB;

	/* NOTE when edit here, don't forget to remove the cache file */
	protected static $zip_name_parts = array (
		'5.5' => array(
			array('crt' => 'vc11', 'arch' => 'x86'),
			array('crt' => 'vc11', 'arch' => 'x64'),
		),
		'5.4' => array(
			array('crt' => 'vc9', 'arch' => 'x86'),
		),
		'5.3' => array(
			array('crt' => 'vc9', 'arch' => 'x86'),
		),
	);

	public static function dllDownloadUrlExistsCached($name, $version)
	{
		if (!file_exists(self::$cache_db)) {
			return NULL;
		}

		$db = (array)unserialize(file_get_contents(self::$cache_db));

		foreach($db as $ext => $data) {
			if ($ext != $name) {
				continue;
			}

			if (isset($data[$version])) {
				return $data[$version];
			}
		}

		/* We're here means no cache yet */
		return NULL;
	}

	public static function cacheDllDownloadInfo($name, $version, $data)
	{
		$db = array();

		if (file_exists(self::$cache_db)) {
			$db = (array)unserialize(file_get_contents(self::$cache_db));
		}

		if (!isset($db[$name])) {
			$db[$name] = array();
		}
		
		$db[$name][$version] = $data;

		return false !== file_put_contents(self::$cache_db, serialize($db), LOCK_EX);
	}

	/* need always both ts/nts for each branch */
	public static function getZipFileList($name, $version)
	{
		$ret = array();

		foreach (self::$zip_name_parts as $branch => $data) {
			foreach ($data as $set) {
				$pref = "php_" . $name . "-" . $version . "-" . $branch;
				$suf = $set["crt"] . "-" . $set["arch"] . ".zip";

				if (!isset($ret[$branch])) {
					$ret[$branch] = array();
				}
				if (!isset($ret[$branch][$set["arch"]])) {
					$ret[$branch][$set["arch"]] = array();
				}
				$ret[$branch][$set["arch"]][] = strtolower($pref . "-nts-" . $suf);
				$ret[$branch][$set["arch"]][] = strtolower($pref . "-ts-" . $suf);
			}
		}

		return $ret;
	}

	public static function getDllDownloadUrls($name, $version, $cache = true)
	{
		$host = 'windows.php.net';
		$port = 80;
		$uri = "/downloads/pecl/releases/" . strtolower($name) . "/" . $version;
		$ret = array();

		if ($cache) {
			$ret = self::dllDownloadUrlExistsCached($name, $version);
			if ($ret) {
				return $ret;
			}
		}

		$fp = fsockopen($host, $port);
		if (!$fp) {
			return NULL;
		}

		$hdrs = "GET $uri/ HTTP/1.0\r\nHost: $host\r\nConnection: close\r\n\r\n";
		$r = fwrite($fp, $hdrs);
		if (false === $r || $r != strlen($hdrs)) {
			fclose($fp);
			return NULL;
		}

		/* so much should be enough */
		$r = '';
		while (!feof($fp)) {
			$r .= fread($fp, 32768);
		}
		if (preg_match(',HTTP/\d\.\d 200 .*,', $r) < 1) {
			fclose($fp);
			return NULL;
		}

		fclose($fp);

		foreach (self::getZipFileList($name, $version) as $branch => $data) {
			foreach ($data as $arch => $zips) {
				$branch_ok = true;

				foreach ($zips as $zip) {
					$branch_ok = $branch_ok && strpos(strtolower($r), $zip);
				}

				if ($branch_ok) {
					$tmp = array();
					foreach ($zips as $zip) {
						$tmp[] = "http://$host$uri/$zip";
					}

					if (!isset($ret[$branch])) {
						$ret[$branch] = array();
					}
					$ret[$branch] = array_merge($ret[$branch], $tmp);
				}
			}
			
		}
	
		if ($cache) {
			self::cacheDllDownloadInfo($name, $version, $ret);
		}
		
		return $ret;
	}
}

