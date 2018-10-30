<?php

namespace App;

/**
 * A thin vanilla PHP PDO wrapper to ease accessing the database.
 */
class Database extends \PDO
{
    /**
     * Class constructor. same as PDO constructor to connect to database with
     * option to extend it in the future.
     */
    public function __construct($dsn, $username = '', $password = '', array $options = [])
    {
        parent::__construct($dsn, $username, $password, $options);
    }
}
