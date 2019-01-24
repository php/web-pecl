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

namespace App\Utils;

/**
 * Pagination utility.
 */
class Pagination
{
    const DEFAULT_ITEMS_PER_PAGE = 15;
    private $itemsPerPage;
    private $numberOfItems = 0;
    private $currentPage = 1;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->itemsPerPage = self::DEFAULT_ITEMS_PER_PAGE;
    }

    /**
     * Set how many items are visible per page.
     */
    public function setItemsPerPage($itemsPerPage)
    {
        $this->itemsPerPage = $itemsPerPage;
    }

    /**
     * Return current number of items set per page.
     */
    public function getItemsPerPage()
    {
        return $this->itemsPerPage;
    }

    /**
     * Set total number of items.
     */
    public function setNumberOfItems($count)
    {
        $this->numberOfItems = $count;
    }

    /**
     * Current page to get results.
     */
    public function setCurrentPage($page)
    {
        $this->currentPage = $page;
    }

    /**
     * First item in the displayed result set.
     */
    public function getFrom()
    {
        $from = $this->currentPage * $this->itemsPerPage - $this->itemsPerPage + 1;
        if ($from > $this->numberOfItems) {
            return $this->numberOfItems;
        }

        return $from;
    }

    /**
     * Last item in the displayed result set.
     */
    public function getTo()
    {
        $to = $this->currentPage * $this->itemsPerPage;

        if ($to > $this->numberOfItems) {
            return $this->numberOfItems;
        }

        return $to;
    }

    /**
     * Get total number of pages.
     */
    public function getNumberOfPages()
    {
        return ceil($this->numberOfItems / $this->itemsPerPage);
    }
}
