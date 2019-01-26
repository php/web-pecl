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

namespace App\Template;

/**
 * Context represents a template variable scope where $this pseudo-variable can
 * be used in the templates and context methods can be called as $this->method().
 */
class Context
{
    /**
     * Templates directory.
     *
     * @var string
     */
    private $dir;

    /**
     * The current processed template or snippet file.
     *
     * @var string
     */
    private $current;

    /**
     * All assigned and set variables for the template.
     *
     * @var array
     */
    private $variables = [];

    /**
     * Pool of blocks for the template context.
     *
     * @var array
     */
    private $blocks = [];

    /**
     * Parent templates extended by child templates.
     *
     * @var array
     */
    public $tree = [];

    /**
     * Registered callables.
     *
     * @var array
     */
    private $callables = [];

    /**
     * Current nesting level of the output buffering mechanism.
     *
     * @var int
     */
    private $bufferLevel = 0;

    /**
     * Class constructor.
     *
     * @param string $dir
     */
    public function __construct(
        $dir,
        array $variables = [],
        array $callables = []
    ) {
        $this->dir = $dir;
        $this->variables = $variables;
        $this->callables = $callables;
    }

    /**
     * Sets a parent layout for the given template. Additional variables in the
     * parent scope can be defined via the second argument.
     *
     * @param string $parent
     *
     * @return void
     */
    public function extend($parent, array $variables = [])
    {
        if (isset($this->tree[$this->current])) {
            throw new \Exception('Extending '.$parent.' is not possible.');
        }

        $this->tree[$this->current] = [$parent, $variables];
    }

    /**
     * Return a block content from the pool by name.
     *
     * @param string $name
     *
     * @return string
     */
    public function block($name)
    {
        return isset($this->blocks[$name]) ? $this->blocks[$name] : '';
    }

    /**
     * Starts a new template block. Under the hood a simple separate output
     * buffering is used to capture the block content. Content can be also
     * appended to previously set same block name.
     *
     * @param string $name
     *
     * @return void
     */
    public function start($name)
    {
        $this->blocks[$name] = '';

        ++$this->bufferLevel;

        ob_start();
    }

    /**
     * Append content to a template block. If no block with the key name exists
     * yet it starts a new one.
     *
     * @param string $name
     *
     * @return void
     */
    public function append($name)
    {
        if (!isset($this->blocks[$name])) {
            $this->blocks[$name] = '';
        }

        ++$this->bufferLevel;

        ob_start();
    }

    /**
     * Ends block output buffering and stores its content into the pool.
     *
     * @param string $name
     *
     * @return void
     */
    public function end($name)
    {
        --$this->bufferLevel;

        $content = ob_get_clean();

        if (!empty($this->blocks[$name])) {
            $this->blocks[$name] .= $content;
        } else {
            $this->blocks[$name] = $content;
        }
    }

    /**
     * Include template file into existing template.
     *
     * @param string $template
     *
     * @return mixed
     */
    public function insert($template, array $variables = [])
    {
        if (count($variables) > extract($variables, EXTR_SKIP)) {
            throw new \Exception(
                'Variables with numeric names $0, $1... cannot be imported to scope '.$template
            );
        }

        return include $this->dir.'/'.$template;
    }

    /**
     * Scalpel when preventing XSS vulnerabilities. This escapes given string
     * and still preserves certain characters as HTML.
     *
     * @param string $string
     *
     * @return string
     */
    public function e($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Hammer when protecting against XSS. Sanitize strings and replace all
     * characters to their applicable HTML entities from it.
     *
     * @param string $string
     *
     * @return string
     */
    public function noHtml($string)
    {
        return htmlentities($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * A proxy to call registered callable.
     *
     * @param string $method
     *
     * @return mixed
     */
    public function __call($method, array $arguments)
    {
        if (isset($this->callables[$method])) {
            return call_user_func_array($this->callables[$method], $arguments);
        }
    }
}
