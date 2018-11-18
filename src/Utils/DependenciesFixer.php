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

namespace App\Utils;

use App\Database;
use \PEAR_Common as PEAR_Common;

class DependenciesFixer
{
    private $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function fix()
    {
        $releases = $this->database->run("SELECT r.id, concat_ws('-', p.name, r.version) FROM packages p, releases r WHERE r.package = p.id")->fetchAll(\PDO::FETCH_KEY_PAIR);

        print "<h2>Deleting Existing Dependencies...</h2>\n";

        $statement = $this->database->run("DELETE FROM deps");

        print $statement->rowCount()." rows deleted<br />\n";
        print "<h2>Inserting New Dependencies...</h2>\n";

        $statement = $this->database->run("SELECT package, `release`, fullpath FROM files");

        $pc = new PEAR_Common;

        foreach ($statement->fetchAll() as $row) {
            printf("<h3>%s (package %d, release %d):</h3>\n",
                basename($row['fullpath']),
                $row['package'],
                $row['release']
            );

            if (!file_exists($row['fullpath'])) {
                continue;
            }

            $pkginfo = $pc->infoFromTgzFile($row['fullpath']);

            if (empty($pkginfo['release_deps'])) {
                printf("%s : no dependencies<br />\n", $releases[$row['release']]);

                continue;
            }

            foreach ($pkginfo['release_deps'] as $dep) {
                if ($dep['rel']) {
                    $dep['relation'] = $dep['rel'];
                    unset($dep['rel']);
                }

                $fields = implode(',', array_keys($dep));
                $values = array_values($dep);
                $phs = substr(str_repeat('?,', count($values) + 2), 0, -1);

                $sql = "INSERT INTO deps (package, `release`, $fields) VALUES($phs)";

                $statement = $this->database->prepare($sql);

                $values = array_merge([$row['package'], $row['release']], $values);

                if ($dep['type'] === 'php') {
                    printf("%s : php %s %s<br />\n",
                        $releases[$row['release']],
                        $dep['relation'],
                        $dep['version']
                    );
                } elseif ($dep['relation'] === 'has') {
                    printf("%s : (%s) %s %s<br />\n",
                        $releases[$row['release']],
                        $dep['type'],
                        $dep['name']
                    );
                } else {
                    printf("%s : (%s) %s %s %s<br />\n",
                        $releases[$row['release']],
                        $dep['type'],
                        $dep['name'],
                        $dep['relation'],
                        $dep['version']
                    );
                }

                $statement->execute($values);
            }
        }
    }
}
