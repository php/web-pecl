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
  | Authors: Stig S. Bakken <ssb@fast.no>                                |
  |          Peter Kokot <petk@php.net>                                  |
  +----------------------------------------------------------------------+
*/

namespace App\Fixtures;

use App\Database;
use App\Entity\Category;
use App\Entity\Package;
use App\Rest;
use Faker\Factory;

/**
 * Data fixtures for database. It uses Faker library for generating fixtures
 * data.
 */
class AppFixtures
{
    private $database;
    private $rest;

    /**
     * Set database handler dependency.
     */
    public function setDatabase(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Set REST generator dependency.
     */
    public function setRest(Rest $rest)
    {
        $this->rest = $rest;
    }

    /**
     * Insert data in the categories tables.
     */
    public function insertCategories()
    {
        $category = new Category();
        $category->setDatabase($this->database);
        $category->setRest($this->rest);

        $catids = [];
        foreach ($this->getCategories() as $key => $item) {
            if (is_array($item)) {
                $name = $key;
                $description = $item['description'];
                $parent = $item['parent'];
            } else {
                $name = $item;
                $description = null;
                $parent = null;
            }

            if (!empty($parent)) {
                $parent = $catids[$parent];
            }

            $catid = $category->add([
                'name' => $name,
                'description' => $description,
                'parent' => $parent,
            ]);

            $catids[$name] = $catid;
        }
    }

    /**
     * Insert fixtures in users table.
     */
    public function insertUsers()
    {
        $users = [];
        foreach (explode("\n", $this->getUsers()) as $line) {
            $line = trim($line);

            if (empty($line)) {
                continue;
            }

            $tmp = explode(";", trim($line));
            $users[$tmp[0]]['user'] = $tmp[0];
            $users[$tmp[0]]['pw'] = password_hash('password', PASSWORD_DEFAULT);
            $users[$tmp[0]]['name'] = $tmp[1];
            $users[$tmp[0]]['email'] = $tmp[2];
            $users[$tmp[0]]['admin'] = $tmp[3];
        }

        $sql = "INSERT INTO users (
                    handle,
                    `password`,
                    `name`,
                    email,
                    registered,
                    showemail,
                    created,
                    createdby,
                    `admin`
                ) VALUES (
                    :handle,
                    :password,
                    :name,
                    :email,
                    1,
                    1,
                    :created,
                    :createdby,
                    :admin
                )
        ";

        foreach ($users as $username => $info) {
            if (empty($info['email'])) {
                $email = "$username@example.com";
            } else {
                $email = $info['email'];
            }

            $this->database->run($sql, [
                ':handle'    => $username,
                ':password'  => $info['pw'],
                ':name'      => $info['name'],
                ':email'     => $email,
                ':created'   => gmdate("Y-m-d H:i:s"),
                ':createdby' => 'imported',
                ':admin'     => (int)$info['admin'],
            ]);
        }
    }

    /**
     * Insert data into packages table.
     */
    public function insertPackages()
    {
        $faker = Factory::create();
        $packageEntity = new Package();
        $packageEntity->setDatabase($this->database);
        $packageEntity->setRest($this->rest);

        $categories = $this->database->run("SELECT id, name FROM categories")->fetchAll(\PDO::FETCH_KEY_PAIR);
        $users = $this->database->run("SELECT handle, name FROM users")->fetchAll(\PDO::FETCH_KEY_PAIR);

        for ($i = 1; $i < 1000; $i++) {
            $packageEntity->add([
                'name'        => $faker->word.$i,
                'type'        => 'pecl',
                'license'     => 'PHP License',
                'summary'     => $faker->sentence,
                'description' => $faker->text,
                'category'    => array_rand($categories, 1),
                'lead'        => array_rand($users, 1),
            ]);
        }
    }

    /**
     * Categories.
     */
    public function getCategories()
    {
        return [
            'Authentication',
            'Benchmarking',
            'Caching',
            'Configuration',
            'Console',
            'Database',
            'Date and Time',
            'Encryption',
            'Event',
            'File Formats',
            'File System',
            'Gtk Components',
            'Gtk2 Components',
            'GUI',
            'HTML',
            'HTTP',
            'Images',
            'Internationalization',
            'Languages',
            'Logging',
            'Mail',
            'Math',
            'Multimedia',
            'Audio' => [
                'description' => '',
                'parent' => 'Multimedia',
            ],
            'Networking',
            'Numbers',
            'Payment',
            'PHP',
            'Processing',
            'QA Tools',
            'Scheduling',
            'Science',
            'Search Engine',
            'Security',
            'Semantic Web',
            'Streams',
            'Structures',
            'System',
            'Text',
            'Tools and Utilities',
            'Testing' => [
                'description' => '',
                'parent' => 'Tools and Utilities',
            ],
            'Validate',
            'Version Control' => [
                'description' => '',
                'parent' => 'Tools and Utilities',
            ],
            'Virtualization',
            'Web Services',
            'XML',
        ];
    }

    /**
     * Get users data.
     */
    public function getUsers()
    {
        return '
            admin;John Doe;;1
            alexmerz;Alexander Merz;;0
            chregu;Christian Stocker;;0
            cox;Tomas V.V.Cox;;1
            jmcastagnetto;Jesus M. Castagnetto;;0
            jon;Jon Parise;;0
            kaltroft;Martin Kaltroft;;0
            mj;Martin Jansen;;1
            sebastian;Sebastian Bergmann;;0
            sn;Sebastian Nohn;sn@example.com;0
            ssb;Stig S. Bakken;ssb@example.com;1
            zyprexia;Dave Mertens;dmertens@example.com;0
            jimw;Jim Winstead;jimw@example.com;1
            andi;Andi Gutmans;andi@example.com;1
            rasmus;;;1
            zeev;;;1
            jimw;;;1
            andrei;;;1
            thies;;;1
        ';
    }

}
