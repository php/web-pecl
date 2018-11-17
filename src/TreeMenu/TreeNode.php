<?php

/**
 * This class is based on the original class from the  HTML_TreeMenu package
 * https://pear.php.net/package/HTML_TreeMenu licensed under the 3-Clause BSD
 * License https://opensource.org/licenses/BSD-3-Clause
 *
 *   Copyright (c) 2002-2005, Richard Heyes, Harald Radi
 *   All rights reserved.
 *
 *   Redistribution and use in source and binary forms, with or without
 *   modification, are permitted provided that the following conditions
 *   are met:
 *
 *   o Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *   o Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in the
 *     documentation and/or other materials provided with the distribution.
 *   o The names of the authors may not be used to endorse or promote
 *     products derived from this software without specific prior written
 *     permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

namespace App\TreeMenu;

/**
 * This class is supplementary to the above and provides a way to
 * add nodes to the tree. A node can have other nodes added to it.
 */
class TreeNode
{
    /**
     * The text for this node.
     */
    var $text;

    /**
     * The link for this node.
     */
    var $link;

    /**
     * The icon for this node.
     */
    var $icon;

    /**
     * The icon to show when expanded for this node.
     */
    var $expandedIcon;

    /**
     * The css class for this node
     */
    var $cssClass;

    /**
     * The link target for this node
     */
    var $linkTarget;

    /**
     * Indexed array of subnodes
     * @var array
     */
    var $items;

    /**
     * Whether this node is expanded or not
     * @var bool
     */
    var $expanded;

    /**
     * Whether this node is dynamic or not
     * @var bool
     */
    var $isDynamic;

    /**
     * Should this node be made visible?
     * @var bool
     */
    var $ensureVisible;

    /**
     * The parent node. Null if top level
     * @var TreeNode
     */
    var $parent;

    /**
     * Javascript event handlers;
     * @var array
     */
    var $events;

    /**
     * Constructor
     *
     * @param array $options An array of options which you can pass to change
     *                       the way this node looks/acts. This can consist of:
     *
     *                         o text          The title of the node,
     *                                         defaults to blank
     *                         o link          The link for the node,
     *                                         defaults to blank
     *                         o icon          The icon for the node,
     *                                         defaults to blank
     *                         o expandedIcon  The icon to show when the node
     *                                         is expanded
     *                         o cssClass      The CSS class for this node,
     *                                         defaults to blank
     *                         o expanded      The default expanded status of
     *                                         this node, defaults to false
     *                                         This doesn't affect non dynamic
     *                                         presentation types
     *                         o linkTarget    Target for the links.
     *                                         Defaults to linkTarget of the
     *                                         HTML_TreeMenu_Presentation.
     *                         o isDynamic     If this node is dynamic or not.
     *                                         Only affects certain
     *                                         presentation types.
     *                         o ensureVisible If true this node will be made
     *                                         visible despite the expanded
     *                                         settings, and client side
     *                                         persistence. Will not affect
     *                                         some presentation styles, such as
     *                                         Listbox.
     *                                         Default is false
     *
     * @param array $events  An array of javascript events and the corresponding
     *                       event handlers. Additionally to the standard
     *                       JavaScript events you can specify handlers for the
     *                       'onexpand', 'oncollapse' and 'ontoggle' events
     *                       which will be fired whenever a node is collapsed
     *                       and/or expanded.
     */
    public function __construct(array $options = [], array $events = [])
    {
        $this->text          = '';
        $this->link          = '';
        $this->icon          = '';
        $this->expandedIcon  = '';
        $this->cssClass      = '';
        $this->expanded      = false;
        $this->isDynamic     = true;
        $this->ensureVisible = false;
        $this->linkTarget    = null;

        $this->parent = null;
        $this->events = $events;

        foreach ($options as $option => $value) {
            $this->$option = $value;
        }
    }

    /**
     * Allows setting of various parameters after the initial constructor call
     *
     * Possible options you can set are:
     *  o text
     *  o link
     *  o icon
     *  o cssClass
     *  o expanded
     *  o isDynamic
     *  o ensureVisible
     *
     * NOTE:  The same options as in TreeNode
     *
     * @param string $option Option to set
     * @param string $value  Value to set the option to
     *
     * @return void
     */
    public function setOption($option, $value)
    {
        $this->$option = $value;
    }

    /**
     * Adds a new subnode to this node.
     *
     * @return int
     */
    public function addItem(TreeNode $node)
    {
        $node->parent  = $this;
        $this->items[] = $node;

        /*
         * If the subnode has ensureVisible set it needs to be handled, and all
         * parents set accordingly.
         */
        if ($node->ensureVisible) {
            $this->ensureIsVisible();
        }

        return $this->items[count($this->items) - 1];
    }

    /**
     * Private function to handle ensureVisible stuff
     *
     * @return void
     */
    private function ensureIsVisible()
    {
        $this->ensureVisible = true;
        $this->expanded      = true;

        if (!is_null($this->parent)) {
            $this->parent->ensureIsVisible();
        }
    }
}
