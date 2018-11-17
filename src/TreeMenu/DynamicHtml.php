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
 * This class is a presentation class for the tree structure created using the
 * TreeNode. It presents the traditional tree, static for browsers that can't
 * handle the DHTML.
 */
class DynamicHtml extends Presentation
{
    /**
     * Dynamic status of the treemenu. If true (default) this has no effect. If
     * false it will override all dynamic status vars and set the menu to be
     * fully expanded an non-dynamic.
     * @var bool
     */
    var $isDynamic;

    /**
     * Path to the images
     * @var string
     */
    var $images;

    /**
     * Target for the links generated
     * @var string
     */
    var $linkTarget;

    /**
     * Whether to use clientside persistence or not
     * @var bool
     */
    var $usePersistence;

    /**
     * The default CSS class for the nodes
     * @var string
     */
    var $defaultClass;

    /**
     * Whether to skip first level branch images
     * @var bool
     */
    var $noTopLevelImages;

    /**
     * Name of Jabbascript object to use
     * @var string
     */
    var $jsObjectName;

    /**
     * Constructor
     *
     * Takes the tree structure as an argument and an array of options which can
     * consist of:
     *
     *  o images            -  The path to the images folder.
     *                         Defaults to "images"
     *  o linkTarget        -  The target for the link.
     *                         Defaults to "_self"
     *  o defaultClass      -  The default CSS class to apply to a node.
     *                         Default is none.
     *  o usePersistence    -  Whether to use clientside persistence. This
     *                         persistence is achieved using cookies.
     *                         Default is true.
     *  o noTopLevelImages  -  Whether to skip displaying the first level of
     *                         images if there is multiple top level branches.
     *  o maxDepth          -  The maximum depth of indentation. Useful for
     *                         ensuring deeply nested trees don't go way off to
     *                         the right of your page etc.
     *                         Defaults to no limit.
     *  o jsObjectName      -  Name to use for jabbascript object. Set this if
     *                         you have different menus that should maintain
     *                         their persistence information separately.
     *
     * And also a boolean for whether the entire tree is dynamic or not. This
     * overrides any perNode dynamic settings.
     *
     * @param array         $options    Array of options
     * @param bool          $isDynamic  Whether the tree is dynamic or not
     */
    public function __construct(TreeMenu $structure, array $options = [], $isDynamic = true)
    {
        parent::__construct($structure);
        $this->isDynamic = $isDynamic;

        // Defaults
        $this->images           = 'images';
        $this->maxDepth         = 0;        // No limit
        $this->linkTarget       = '_self';
        $this->jsObjectName     = 'objTreeMenu';
        $this->defaultClass     = '';
        $this->usePersistence   = true;
        $this->noTopLevelImages = false;

        foreach ($options as $option => $value) {
            $this->$option = $value;
        }
    }

    /**
     * Returns the HTML for the menu.
     *
     * This method can be used instead of
     * {@link HTML_TreeMenu_Presentation::printMenu()}
     * to use the menu system with a template system.
     *
     * @return string The HTML for the menu
     */
    public function toHTML()
    {
        static $count = 0;

        $menuObj = $this->jsObjectName . '_' . ++$count;

        $html  = "\n";
        $html .= '<script type="text/javascript">' . "\n//<![CDATA[\n\t";
        $html .= sprintf('%s = new TreeMenu("%s", "%s", "%s", "%s", %s, %s);',
                         $menuObj,
                         $this->images,
                         $menuObj,
                         $this->linkTarget,
                         $this->defaultClass,
                         $this->usePersistence ? 'true' : 'false',
                         $this->noTopLevelImages ? 'true' : 'false');

        $html .= "\n";

        /*
         * Loop through subnodes
         */
        if (isset($this->menu->items)) {
            for ($i=0; $i<count($this->menu->items); $i++) {
                $html .= $this->nodeToHTML($this->menu->items[$i], $menuObj);
            }
        }

        $html .= sprintf("\n\t%s.drawMenu();", $menuObj);
        $html .= sprintf("\n\t%s.writeOutput();", $menuObj);

        if ($this->usePersistence && $this->isDynamic) {
            $html .= sprintf("\n\t%s.resetBranches();", $menuObj);
        }
        $html .= "\n// ]]>\n</script>";

        return $html;
    }

    /**
     * Prints a node of the menu
     *
     * @param mixed         $prefix         prefix
     * @param string        $return         default to 'newNode'
     * @param int           $currentDepth   default to 0
     * @param mixed         $maxDepthPrefix default to null
     *
     * @return string
     */
    private function nodeToHTML(TreeNode $nodeObj,
                         $prefix,
                         $return         = 'newNode',
                         $currentDepth   = 0,
                         $maxDepthPrefix = null)
    {
        $prefix = empty($maxDepthPrefix) ? $prefix : $maxDepthPrefix;

        $expanded  = $this->isDynamic ?
                        ($nodeObj->expanded  ? 'true' : 'false') : 'true';
        $isDynamic = $this->isDynamic ?
                        ($nodeObj->isDynamic ? 'true' : 'false') : 'false';
        $html      = sprintf("\t %s = %s.addItem(new TreeNode" .
                        "('%s', %s, %s, %s, %s, '%s', '%s', %s));\n",
                        $return,
                        $prefix,
                        str_replace("'", "\\'", $nodeObj->text),
                        !empty($nodeObj->icon) ? "'" . $nodeObj->icon . "'" : 'null',
                        !empty($nodeObj->link) ? "'" . $nodeObj->link . "'" : 'null',
                        $expanded,
                        $isDynamic,
                        $nodeObj->cssClass,
                        $nodeObj->linkTarget,
                        !empty($nodeObj->expandedIcon) ?
                            "'" . $nodeObj->expandedIcon . "'" : 'null');

        foreach ($nodeObj->events as $event => $handler) {
            $html .= sprintf("\t %s.setEvent('%s', '%s');\n",
                             $return,
                             $event,
                             str_replace(["\r", "\n", "'"],
                                ['\r', '\n', "\'"],
                             $handler));
        }

        if ($this->maxDepth > 0 AND $currentDepth == $this->maxDepth) {
            $maxDepthPrefix = $prefix;
        }

        /*
         * Loop through subnodes
         */
        if (!empty($nodeObj->items)) {
            for ($i=0; $i<count($nodeObj->items); $i++) {
                $html .= $this->nodeToHTML($nodeObj->items[$i],
                                            $return,
                                            $return . '_' . ($i + 1),
                                            $currentDepth + 1,
                                            $maxDepthPrefix);
            }
        }

        return $html;
    }
}
