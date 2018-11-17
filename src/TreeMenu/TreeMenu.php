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
 * A simple couple of PHP code and some not so simple Jabbascript which produces
 * a tree menu. In IE this menu is dynamic, with branches being collapsable. In
 * IE5+ the status of the collapsed/open branches persists across page refreshes.
 * In any other browser the tree is static.
 *
 * After installing the package, copy the example php script to your servers
 * document root. Also place the TreeMenu.js and the images folder in the same
 * place. Running the script should then produce the tree.
 *
 * Thanks go to Chip Chapin (http://www.chipchapin.com) for many excellent ideas
 * and improvements.
 */
class TreeMenu
{
    /**
     * Indexed array of subnodes
     * @var array
     */
    public $items;

    /**
     * This function adds an item to the the tree.
     *
     * @param TreeNode &$node The node to add.
     *                             This object should be a Node object.
     *
     * @return int Returns a reference to the new node inside the tree.
     */
    public function addItem(TreeNode $node)
    {
        $this->items[] = $node;

        return $this->items[count($this->items) - 1];
    }

    /**
     * Import method for creating {@link HTML_TreeMenu} objects/structures
     * out of existing tree objects/structures.
     *
     * Currently supported are Wolfram Kriesings' PEAR Tree class, and
     * Richard Heyes' Tree class ({@link http://www.phpguru.org/}). This
     * method is intended to be used statically, eg:
     * <code>
     * $treeMenu = &HTML_TreeMenu::createFromStructure($myTreeStructureObj);
     * </code>
     *
     * @param array $params An array of parameters that determine
     *                      how the import happens. This can consist of:
     *                          structure   => The tree structure
     *                          type        => The type of the structure, currently
     *                                         can be either 'heyes' or 'kriesing'
     *                          nodeOptions => Default options for each node
     *
     * @return TreeMenu The resulting TreeMenu object
     */
    public static function createFromStructure(array $params)
    {
        if (!isset($params['nodeOptions'])) {
            $params['nodeOptions'] = [];
        }

        switch (@$params['type']) {

        /*
         * Wolfram Kriesings' PEAR Tree class
         */
        case 'kriesing':
            $className   = get_class($params['structure']->dataSourceClass);
            $className   = strtolower($className);
            $isXMLStruct = strpos($className, '_xml') !== false ? true : false;

            // Get the entire tree, the $nodes are sorted like in the tree view
            // from top to bottom, so we can easily put them in the nodes
            $nodes = $params['structure']->getNode();

            // Make a new menu and fill it with the values from the tree
            $treeMenu = new HTML_TreeMenu();
            // we need the current node as the reference
            $curNode[0] = $treeMenu;

            foreach ($nodes as $aNode) {
                $events = [];
                $data   = [];

                /* In an XML, all the attributes are saved in an array, but
                 * since they might be  used as the parameters, we simply
                 * extract them here if we handle an XML-structure
                 */
                if ( $isXMLStruct && sizeof($aNode['attributes'])) {
                    foreach ($aNode['attributes'] as $key=>$val) {
                        if ( !$aNode[$key] ) {
                            // dont overwrite existing values
                            $aNode[$key] = $val;
                        }
                    }
                }

                // Process all the data that are saved in $aNode and put them
                // in the data and/or events array
                foreach ($aNode as $key=>$val) {
                    if (!is_array($val)) {
                        // Dont get the recursive data in here! they are
                        // always arrays
                        if (substr($key, 0, 2) == 'on') {
                            // get the events
                            $events[$key] = $val;
                        }

                        // I put it in data too, so in case an options starts
                        // with 'on' its also passed to the node ... not too
                        // cool i know
                        $data[$key] = $val;
                    }
                }

                // Normally the text is in 'name' in the Tree class, so we
                // check both but 'text' is used if found
                $data['text'] = $aNode['text'] ?
                    $aNode['text'] : $aNode['name'];

                // Add the item to the proper node
                $thisNode = &$curNode[$aNode['level']]->addItem(new
                            Node($data, $events));

                $curNode[$aNode['level']+1] = &$thisNode;
            }
            break;

        /*
         * Richard Heyes' (me!) second (array based) Tree class
         */
        case 'heyes_array':
            // Need to create a HTML_TreeMenu object ?
            if (!isset($params['treeMenu'])) {
                $treeMenu = new TreeMenu();
                $parentID = 0;
            } else {
                $treeMenu = &$params['treeMenu'];
                $parentID = $params['parentID'];
            }

            // Loop thru the trees nodes
            foreach ($params['structure']->getChildren($parentID)
                    as $nodeID) {
                $data       = $params['structure']->getData($nodeID);
                $parentNode = &$treeMenu->addItem(new
                    Node(array_merge($params['nodeOptions'], $data)));

                // Recurse ?
                if ($params['structure']->hasChildren($nodeID)) {
                    $recurseParams['type']        = 'heyes_array';
                    $recurseParams['parentID']    = $nodeID;
                    $recurseParams['nodeOptions'] = $params['nodeOptions'];
                    $recurseParams['structure']   = &$params['structure'];
                    $recurseParams['treeMenu']    = &$parentNode;
                    HTML_TreeMenu::createFromStructure($recurseParams);
                }
            }

            break;

        /*
         * Richard Heyes' (me!) original OO based Tree class
         */
        case 'heyes':
        default:
            // Need to create a TreeMenu object ?
            if (!isset($params['treeMenu'])) {
                $treeMenu = new TreeMenu();
            } else {
                $treeMenu = &$params['treeMenu'];
            }

            // Loop thru the trees nodes
            foreach ($params['structure']->nodes->nodes as $node) {
                $tag        = $node->getTag();
                $parentNode = &$treeMenu->addItem(new
                 Node(array_merge($params['nodeOptions'], $tag)));

                // Recurse ?
                if (!empty($node->nodes->nodes)) {
                    $recurseParams['structure']   = $node;
                    $recurseParams['nodeOptions'] = $params['nodeOptions'];
                    $recurseParams['treeMenu']    = &$parentNode;
                    TreeMenu::createFromStructure($recurseParams);
                }
            }
            break;

        }
        return $treeMenu;
    }
}
