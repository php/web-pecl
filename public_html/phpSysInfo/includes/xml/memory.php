<?php
//
// phpSysInfo - A PHP System Information Script
// http://phpsysinfo.sourceforge.net/
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
//
// $Id$

//
// xml_memory()
//
function xml_memory () {
    global $sysinfo;
    $mem = $sysinfo->memory();

    $_text = "  <Memory>\n"
           . "    <Free>" . $mem['ram']['t_free'] . "</Free>\n"
           . "    <Used>" . $mem['ram']['t_used'] . "</Used>\n"
           . "    <Total>" . $mem['ram']['total'] . "</Total>\n"
           . "    <Percent>" . $mem['ram']['percent'] . "</Percent>\n"
           . "  </Memory>\n"
           . "  <Swap>\n"
           . "    <Free>" . $mem['swap']['free'] . "</Free>\n"
           . "    <Used>" . $mem['swap']['used'] . "</Used>\n"
           . "    <Total>" . $mem['swap']['total'] . "</Total>\n"
           . "    <Percent>" . $mem['swap']['percent'] . "</Percent>\n"
           . "  </Swap>\n";

    return $_text;
}

//
// xml_memory()
//
function html_memory () {
    global $XPath;
    global $text;

    $scale_factor = 2;

    $ram .= create_bargraph($XPath->getData('/phpsysinfo/Memory/Percent'), $XPath->getData('/phpsysinfo/Memory/Percent'), $scale_factor);
    $ram .= '&nbsp;&nbsp;' . $XPath->getData('/phpsysinfo/Memory/Percent') . '% ';

    $swap .= create_bargraph($XPath->getData('/phpsysinfo/Swap/Percent'), $XPath->getData('/phpsysinfo/Swap/Percent'), $scale_factor);

    $swap .= '&nbsp;&nbsp;' . $XPath->getData('/phpsysinfo/Swap/Percent') . '% ';


    $_text = '<table width="100%" align="center">'
           . '<tr><td align="left" valign="top"><font size="-1"><b>' . $text['type'] . '</b></font></td>'
           . '<td align="left" valign="top"><font size="-1"><b>' . $text['percent'] . '</b></font></td>'
           . '<td align="right" valign="top"><font size="-1"><b>' . $text['free'] . '</b></font></td>'
           . '<td align="right" valign="top"><font size="-1"><b>' . $text['used'] . '</b></font></td>'
           . '<td align="right" valign="top"><font size="-1"><b>' . $text['size'] . '</b></font></td></tr>'
           . '<tr><td align="left" valign="top"><font size="-1">' . $text['phymem'] . '</font></td>'

           . '<td align="left" valign="top"><font size="-1">' . $ram . '</font></td>'
           . '<td align="right" valign="top"><font size="-1">' . format_bytesize($XPath->getData('/phpsysinfo/Memory/Free')) . '</font></td>'
           . '<td align="right" valign="top"><font size="-1">' . format_bytesize($XPath->getData('/phpsysinfo/Memory/Used')) . '</font></td>'
           . '<td align="right" valign="top"><font size="-1">' . format_bytesize($XPath->getData('/phpsysinfo/Memory/Total')) . '</font></td>'

           . '<tr><td align="left" valign="top"><font size="-1">' . $text['swap'] . '</font></td>'
           . '<td align="left" valign="top"><font size="-1">' . $swap . '</font></td>'
           . '<td align="right" valign="top"><font size="-1">' . format_bytesize($XPath->getData('/phpsysinfo/Swap/Free')) . '</font></td>'
           . '<td align="right" valign="top"><font size="-1">' . format_bytesize($XPath->getData('/phpsysinfo/Swap/Used')) . '</font></td>'
           . '<td align="right" valign="top"><font size="-1">' . format_bytesize($XPath->getData('/phpsysinfo/Swap/Total')) . '</font></td>'

           . '</table>';

    return $_text;
}

?>
