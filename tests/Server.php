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
  | Authors: Peter Kokot <petk@php.net>                                  |
  +----------------------------------------------------------------------+
*/

namespace App\Tests;

/**
 * A simple PHP CLI server for testing purposes based on the server written for
 * testing the php-src *.phpt files.
 */
class Server
{
    private $address;
    private $host = 'localhost';
    private $port = 8964;
    private $phpExecutable;
    private $handle;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->address = $this->host.':'.$this->port;
        $this->phpExecutable = 'php';
    }

    /**
     * Set host of the running server.
     */
    public function setHost($host)
    {
        $this->host = $host;
        $this->address = $this->host.':'.$this->port;
    }

    /**
     * Set port on which server will be listening.
     */
    public function setPort($port)
    {
        $this->port = $port;
        $this->address = $this->host.':'.$this->port;
    }

    /**
     * Starts the PHP CLI server and returns the configured address.
     */
    public function start()
    {
        $docRoot = __DIR__;
        $router = __DIR__.'/router.php';

        if (PHP_OS_FAMILY === 'Windows') {
            $descriptorspec = [
                0 => STDIN,
                1 => STDOUT,
                2 => ['pipe', 'w'],
            ];

            $cmd = "{$this->phpExecutable} -t {$docRoot} -n -S ".$this->address." {$router}";
            $this->handle = proc_open(addslashes($cmd), $descriptorspec, $pipes, $docRoot, NULL, ['bypass_shell' => true,  'suppress_errors' => true]);
        } else {
            $descriptorspec = [
                0 => STDIN,
                1 => STDOUT,
                2 => STDERR,
            ];

            $cmd = "exec {$this->phpExecutable} -t {$docRoot} -n -S ".$this->address." {$router} > /dev/null 2>&1";

            $this->handle = proc_open($cmd, $descriptorspec, $pipes, $docRoot);
        }

        // Even when server prints 'Listening on localhost:8964...Press Ctrl-C to quit.'
        // it might not be listening yet. We need to wait until fsockopen() call returns
        $error = "Unable to connect to server\n";

        for ($i=0; $i < 60; $i++) {
            // 50ms per try
            usleep(50000);
            $status = proc_get_status($this->handle);
            $fp = @fsockopen($this->host, $this->port);

            // Failure, the server is no longer running
            if (!($status && $status['running'])) {
                $error = "Server is not running\n";

                break;
            }

            // Success, connected to server
            if ($fp) {
                $error = '';

                break;
            }
        }

        if ($fp) {
            fclose($fp);
        }

        if ($error) {
            echo $error;
            proc_terminate($this->handle);

            exit(1);
        }

        register_shutdown_function([$this, 'shutdown'], $this->handle);

        return $this->address;
    }

    /**
     * Shutdown the running server.
     */
    public function stop()
    {
        $this->shutdown($this->handle);
    }

    /**
     * Shutdown the server.
     */
    public function shutdown($handle)
    {
        proc_terminate($handle);

        // Wait for server to shutdown
        for ($i = 0; $i < 60; $i++) {
            $status = proc_get_status($handle);

            if (!($status && $status['running'])) {
                break;
            }

            usleep(50000);
        }
    }
}
