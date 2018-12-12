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
use Faker\Generator;

/**
 * Data fixtures for database. It uses Faker library for generating fixtures
 * data.
 */
class AppFixtures
{
    private $database;
    private $category;
    private $package;

    /**
     * Faker utility.
     */
    private $faker;

    /**
     * Number of generated users.
     */
    private const PACKAGES_COUNT = 1000;

    /**
     * Number of generated users.
     */
    private const USERS_COUNT = 500;

    /**
     * Class constructor to set injected dependencies.
     */
    public function __construct(Database $database, Generator $faker, Category $category, Package $package)
    {
        $this->database = $database;
        $this->faker = $faker;
        $this->category = $category;
        $this->package = $package;
    }

    /**
     * Insert data in the categories tables.
     */
    public function insertCategories()
    {
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

            $catid = $this->category->add([
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
        $sql = "INSERT INTO users (
                    handle,
                    `password`,
                    `name`,
                    email,
                    registered,
                    showemail,
                    created,
                    createdby,
                    `admin`,
                    homepage
                ) VALUES (
                    :handle,
                    :password,
                    :name,
                    :email,
                    1,
                    1,
                    :created,
                    :createdby,
                    :admin,
                    :homepage
                )
        ";

        $users = $this->getUsers();

        foreach ($users as $username => $data) {
            $this->database->run($sql, [
                ':handle'    => $username,
                ':password'  => $data['password'],
                ':name'      => $data['name'],
                ':email'     => $data['email'],
                ':created'   => gmdate("Y-m-d H:i:s"),
                ':createdby' => 'imported',
                ':admin'     => (int)$data['admin'],
                ':homepage'  => $data['homepage'],
            ]);
        }
    }

    /**
     * Insert data into packages table.
     */
    public function insertPackages()
    {
        $categories = $this->database->run("SELECT id, name FROM categories")->fetchAll(\PDO::FETCH_KEY_PAIR);
        $users = $this->database->run("SELECT handle, name FROM users")->fetchAll(\PDO::FETCH_KEY_PAIR);

        for ($i = 1; $i < self::PACKAGES_COUNT; $i++) {
            $this->package->add([
                'name'        => $this->faker->word.$i,
                'type'        => 'pecl',
                'license'     => 'PHP License',
                'summary'     => $this->faker->sentence,
                'description' => $this->faker->text,
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
     * Get generated demo users data with each user having secret password set to
     * "password".
     */
    public function getUsers()
    {
        $users = [];

        // Demo secret password is always password for all users
        $password = password_hash('password', PASSWORD_DEFAULT);

        // Administrator user
        $users['admin'] = [
            'name' => 'John Doe',
            'password' => $password,
            'email' => 'admin@example.com',
            'admin' => 1,
            'homepage' => 'https://example.com',
        ];

        // Maintainer user
        $users['user'] = [
            'name' => 'Jane Doe',
            'password' => $password,
            'email' => 'user@example.com',
            'admin' => 1,
            'homepage' => 'https://example.org',
        ];

        // More random maintainer level users
        for ($i = 0; $i < self::USERS_COUNT; $i++) {
            $username = $this->faker->unique()->userName;
            $username = substr($username, 0, 16);
            $username = str_replace('.', '', $username);

            $users[$username] = [
                'name' => $this->faker->firstName.' '.$this->faker->lastName,
                'password' => $password,
                'email' => $username.'@example.com',
                'admin' => 0,
                'homepage' => $this->faker->url,
            ];
        }

        return $users;
    }
}
