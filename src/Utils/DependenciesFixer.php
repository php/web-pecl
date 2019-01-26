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
  | Authors:                                                             |
  +----------------------------------------------------------------------+
*/

namespace App\Utils;

use App\Database;
use \PEAR_Config as PEAR_Config;
use \PEAR_PackageFile as PEAR_PackageFile;

class DependenciesFixer
{
    private $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function fix()
    {
        $output = '';

        $releases = $this->database->run("SELECT r.id, concat_ws('-', p.name, r.version) FROM packages p, releases r WHERE r.package = p.id")->fetchAll(\PDO::FETCH_KEY_PAIR);

        $output .= "<h2>Deleting Existing Dependencies...</h2>";

        $statement = $this->database->run("DELETE FROM deps");

        $output .= $statement->rowCount()." rows deleted<br>";
        $output .= "<h2>Inserting New Dependencies...</h2>";

        $statement = $this->database->run("SELECT package, `release`, fullpath FROM files");

        foreach ($statement->fetchAll() as $row) {
            $output .= '<h3>'.basename($row['fullpath']).' (package '.$row['package'].', release '.$row['release'].'):</h3>';

            if (!file_exists($row['fullpath'])) {
                continue;
            }

            $pearConfig = PEAR_Config::singleton();
            $pkg = new PEAR_PackageFile($pearConfig);
            $pkginfo = $pkg->fromTgzFile($row['fullpath'], PEAR_VALIDATE_NORMAL);

            if (empty($pkginfo->getDeps(true))) {
                $output .= $releases[$row['release']].' : no dependencies<br>';

                continue;
            }

            foreach ($pkginfo->getDeps() as $dep) {
                if ($dep['rel']) {
                    $dep['relation'] = $dep['rel'];
                    unset($dep['rel']);
                }

                if (isset($dep['optional'])) {
                    $dep['optional'] = strtolower(trim($dep['optional'])) === 'no' ? 0 : 1;
                }

                if (isset($dep['channel'])) {
                    unset($dep['channel']);
                }

                $fields = implode(',', array_keys($dep));
                $values = array_values($dep);
                $phs = substr(str_repeat('?,', count($values) + 2), 0, -1);

                $sql = "INSERT INTO deps (package, `release`, $fields) VALUES($phs)";

                $statement = $this->database->prepare($sql);

                $values = array_merge([$row['package'], $row['release']], $values);

                if ($dep['type'] === 'php') {
                    $output .= $releases[$row['release']].' : php '.$dep['relation'].' '.$dep['version'].'<br>';
                } elseif ($dep['relation'] === 'has') {
                    $output .= $releases[$row['release']].' : ('.$dep['type'].') '.$dep['name'].'<br>';
                } else {
                    $output .= $releases[$row['release']].' : ('.$dep['type'].') '.$dep['name'].' '.$dep['relation'].' '.$dep['version'].'<br>';
                }

                $statement->execute($values);

                return $output;
            }
        }
    }
}
