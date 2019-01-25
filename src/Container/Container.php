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

namespace App\Container;

use App\Container\Exception\EntryNotFoundException;
use App\Container\Exception\ContainerException;

/**
 * PSR-11 compatible dependency injection container.
 */
class Container implements ContainerInterface
{
    /**
     * All defined services and parameters.
     *
     * @var array
     */
    public $entries = [];

    /**
     * Already retrieved items are stored for faster retrievals in the same run.
     *
     * @var array
     */
    private $store = [];

    /**
     * Services already created to prevent circular references.
     *
     * @var array
     */
    private $locks = [];

    /**
     * Class constructor.
     */
    public function __construct(array $configurations = [])
    {
        $this->entries = $configurations;
    }

    /**
     * Set service.
     */
    public function set($key, $entry)
    {
        $this->entries[$key] = $entry;
    }

    /**
     * Get entry.
     *
     * @return mixed
     */
    public function get($id)
    {
        if (!$this->has($id)) {
            throw new EntryNotFoundException($id.' entry not found.');
        }

        if (!isset($this->store[$id])) {
            $this->store[$id] = $this->createEntry($id);
        }

        return $this->store[$id];
    }

    /**
     * Check if entry is available in the container.
     */
    public function has($id)
    {
        return isset($this->entries[$id]);
    }

    /**
     * Create new entry - service or configuration parameter.
     *
     * @return mixed
     */
    private function createEntry($id)
    {
        $entry = &$this->entries[$id];

        // Entry is not a class but is wrapped in callable.
        if (!class_exists($id) && is_callable($entry)) {
            return $entry($this);
        }

        // Entry is a configuration parameter
        if (!class_exists($id)) {
            return $entry;
        }

        // Entry is a service class
        if (class_exists($id) && !is_callable($entry)) {
            throw new ContainerException($id.' entry must be callable.');
        } elseif (class_exists($id) && isset($this->locks[$id])) {
            throw new ContainerException($id.' entry contains a circular reference.');
        }

        $this->locks[$id] = true;

        return $entry($this);
    }
}
