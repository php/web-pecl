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

/**
 * Service class to post data to the central master.php.net server which can
 * store the data in the database and/or mail notices or requests to PHP.net
 * stuff and servers.
 */
class PhpMasterClient
{
    private $url;

    /**
     * Class constructor.
     */
    public function __construct($url)
    {
        $this->url = $url;
    }

    /**
     * Send data to the API url.
     */
    public function post($data)
    {
        // Get parts of URL
        $this->url = parse_url($this->url);
        if (!$this->url) {
            return "couldn't parse url";
        }

        // Provide defaults for port and query string
        if (!isset($this->url['port'])) {
            $this->url['port'] = "";
        }

        if (!isset($this->url['query'])) {
            $this->url['query'] = "";
        }

        // Build POST string
        $encoded = "";
        foreach ($data as $k => $v) {
            $encoded .= ($encoded ? "&" : "");
            $encoded .= rawurlencode($k) . "=" . rawurlencode($v);
        }

        // Open socket on host
        $fp = fsockopen($this->url['host'], $this->url['port'] ? $this->url['port'] : 80);
        if (!$fp) {
            return "failed to open socket to {$this->url['host']}";
        }

        // Send HTTP 1.0 POST request to host
        fputs($fp, sprintf("POST %s%s%s HTTP/1.0\n", $this->url['path'], $this->url['query'] ? "?" : "", $this->url['query']));
        fputs($fp, "Host: {$this->url['host']}\n");
        fputs($fp, "Content-type: application/x-www-form-urlencoded\n");
        fputs($fp, "Content-length: " . strlen($encoded) . "\n");
        fputs($fp, "Connection: close\n\n");
        fputs($fp, "$encoded\n");

        // Read the first line of data, only accept if 200 OK is sent
        $line = fgets($fp, 1024);
        if (!preg_match('/^HTTP\/1\.. 200/i', $line)) {
            return;
        }

        // Put everything, except the headers to $results
        $results = '';
        $inheader = true;

        while(!feof($fp)) {
            $line = fgets($fp, 1024);

            if ($inheader && ($line == "\n" || $line == "\r\n")) {
                $inheader = FALSE;
            } elseif (!$inheader) {
                $results .= $line;
            }
        }
        fclose($fp);

        // Return with data received
        return $results;
    }
}
