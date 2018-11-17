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
 * This class presents the menu as a listbox
 */
class Listbox extends Presentation
{
    /**
     * The text that is displayed in the first option
     * @var string
     */
    var $promoText;

    /**
     * The character used for indentation
     * @var string
     */
    var $indentChar;

    /**
     * How many of the indent chars to use per indentation level
     * @var int
     */
    var $indentNum;

    /**
     * Target for the links generated
     * @var string
     */
    var $linkTarget;

    /**#@-*/

    /**
     * Constructor
     *
     * @param object $structure The menu structure
     * @param array  $options   Options which affect the display of the listbox.
     *                          These can consist of:
     *                          <pre>
     *                           o promoText  The text that appears at the the
     *                                        top of the listbox
     *                                        Defaults to "Select..."
     *                           o indentChar The character to use for indenting
     *                                        the nodes
     *                                        Defaults to "&nbsp;"
     *                           o indentNum  How many of the indentChars to use
     *                                        per indentation level
     *                                        Defaults to 2
     *                           o linkTarget Target for the links.
     *                                        Defaults to "_self"
     *                           o submitText Text for the submit button.
     *                                        Defaults to "Go"
     *                           </pre>
     */
    public function __construct(TreeMenu $structure, $options = [])
    {
        parent::__construct($structure);

        $this->promoText  = 'Select...';
        $this->indentChar = '&nbsp;';
        $this->indentNum  = 2;
        $this->linkTarget = '_self';
        $this->submitText = 'Go';

        foreach ($options as $option => $value) {
            $this->$option = $value;
        }
    }

    /**
     * Returns the HTML generated
     *
     * @return string
     */
    public function toHTML()
    {
        static $count = 0;

        $nodeHTML = '';

        /*
         * Loop through subnodes
         */
        if (isset($this->menu->items)) {
            for ($i=0; $i<count($this->menu->items); $i++) {
                $nodeHTML .= $this->nodeToHTML($this->menu->items[$i]);
            }
        }

        return sprintf('<form target="%s" action="" onsubmit="var link = ' .
                       'this.%s.options[this.%s.selectedIndex].value; ' .
                       'if (link) {this.action = link; return true} else ' .
                       'return false"><select name="%s">' .
                       '<option value="">%s</option>%s</select> ' .
                       '<input type="submit" value="%s" /></form>',
                       $this->linkTarget,
                       'HTML_TreeMenu_Listbox_' . ++$count,
                       'HTML_TreeMenu_Listbox_' . $count,
                       'HTML_TreeMenu_Listbox_' . $count,
                       $this->promoText,
                       $nodeHTML,
                       $this->submitText);
    }

    /**
     * Returns HTML for a single node
     *
     * @param string        $prefix defaults to empty string
     *
     * @return string
     */
    private function nodeToHTML(TreeNode $node, $prefix = '')
    {
        $html = sprintf('<option value="%s">%s%s</option>',
                        $node->link,
                        $prefix,
                        $node->text);

        /*
         * Loop through subnodes
         */
        if (isset($node->items)) {
            for ($i=0; $i<count($node->items); $i++) {
                $html .= $this->nodeToHTML($node->items[$i],
                    $prefix . str_repeat($this->indentChar,
                    $this->indentNum));
            }
        }

        return $html;
    }
}
