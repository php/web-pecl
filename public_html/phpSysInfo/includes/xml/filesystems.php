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
// xml_filesystems()
//
function xml_filesystems () {
    global $sysinfo;
    $fs = $sysinfo->filesystems();

    $_text = "  <FileSystem>\n";
    for ($i=0; $i<sizeof($fs); $i++) {
        $sum['size'] += $fs[$i]['size'];
        $sum['used'] += $fs[$i]['used'];
        $sum['free'] += $fs[$i]['free']; 
        $_text .="    <Mount>\n"
               . "      <MountPoint>" . $fs[$i]['mount'] . "</MountPoint>\n"
               . "      <Type>" . $fs[$i]['fstype'] . "</Type>\n"
               . "      <Device>" . $fs[$i]['disk'] . "</Device>\n"
               . "      <Percent>" . $fs[$i]['percent'] . "</Percent>\n"
               . "      <Free>" . $fs[$i]['free'] . "</Free>\n"
               . "      <Used>" . $fs[$i]['used'] . "</Used>\n"
               . "      <Size>" . $fs[$i]['size'] . "</Size>\n"
               . "    </Mount>\n";
    }
    $_text .= "  </FileSystem>\n";
    return $_text;
}

//
// html_filesystems()
//
function html_filesystems () {
    global $XPath;
    global $text;

    $scale_factor = 2;

    $_text = '<table width="100%" align="center">'
           . '<tr><td align="left" valign="top"><font size="-1"><b>' . $text['mount'] . '</b></font></td>'
           . '<td align="left" valign="top"><font size="-1"><b>' . $text['type'] . '</b></font></td>'
           . '<td align="left" valign="top"><font size="-1"><b>' . $text['partition'] . '</b></font></td>'
           . '<td align="left" valign="top"><font size="-1"><b>' . $text['percent'] . '</b></font></td>'
           . '<td align="right" valign="top"><font size="-1"><b>' . $text['free'] . '</b></font></td>'
           . '<td align="right" valign="top"><font size="-1"><b>' . $text['used'] . '</b></font></td>'
           . '<td align="right" valign="top"><font size="-1"><b>' . $text['size'] . '</b></font></td></tr>';

    for ($i=1; $i<sizeof($XPath->getDataParts('/phpsysinfo/FileSystem')); $i++) {
        if ($XPath->match("/phpsysinfo/FileSystem/Mount[$i]/MountPoint")) {
            $sum['size'] += $XPath->getData("/phpsysinfo/FileSystem/Mount[$i]/Size");
            $sum['used'] += $XPath->getData("/phpsysinfo/FileSystem/Mount[$i]/Used");
            $sum['free'] += $XPath->getData("/phpsysinfo/FileSystem/Mount[$i]/Free");

            $_text .= "\t<tr>\n";
            $_text .= "\t\t<td align=\"left\" valign=\"top\"><font size=\"-1\">" . $XPath->getData("/phpsysinfo/FileSystem/Mount[$i]/MountPoint") . "</font></td>\n";
            $_text .= "\t\t<td align=\"left\" valign=\"top\"><font size=\"-1\">" . $XPath->getData("/phpsysinfo/FileSystem/Mount[$i]/Type") . "</font></td>\n";
            $_text .= "\t\t<td align=\"left\" valign=\"top\"><font size=\"-1\">" . $XPath->getData("/phpsysinfo/FileSystem/Mount[$i]/Device") . "</font></td>\n";
            $_text .= "\t\t<td align=\"left\" valign=\"top\"><font size=\"-1\">";

            $_text .= create_bargraph($XPath->getData("/phpsysinfo/FileSystem/Mount[$i]/Percent"), $XPath->getData("/phpsysinfo/FileSystem/Mount[$i]/Percent"), $scale_factor, $XPath->getData("/phpsysinfo/FileSystem/Mount[$i]/Type"));

            $_text .= "&nbsp;" . $XPath->getData("/phpsysinfo/FileSystem/Mount[$i]/Percent") . "</font></td>\n";
            $_text .= "\t\t<td align=\"right\" valign=\"top\"><font size=\"-1\">" . format_bytesize($XPath->getData("/phpsysinfo/FileSystem/Mount[$i]/Free")) . "</font></td>\n";
            $_text .= "\t\t<td align=\"right\" valign=\"top\"><font size=\"-1\">" . format_bytesize($XPath->getData("/phpsysinfo/FileSystem/Mount[$i]/Used")) . "</font></td>\n";
            $_text .= "\t\t<td align=\"right\" valign=\"top\"><font size=\"-1\">" . format_bytesize($XPath->getData("/phpsysinfo/FileSystem/Mount[$i]/Size")) . "</font></td>\n";
            $_text .= "\t</tr>\n";
        }
    }

    $_text .= '<tr><td colspan="3" align="right" valign="top"><font size="-1"><i>' . $text['totals'] . ' :&nbsp;&nbsp;</i></font></td>';
    $_text .= "\t\t<td align=\"left\" valign=\"top\"><font size=\"-1\">";

    $sum_percent = round(($sum['used'] * 100) / $sum['size']);
    $_text .= create_bargraph($sum_percent, $sum_percent, $scale_factor);

    $_text .= "&nbsp;" . $sum_percent . "%" .  "</font></td>\n";

    $_text .= '<td align="right" valign="top"><font size="-1">' . format_bytesize($sum['free']) . '</font></td>'
           . '<td align="right" valign="top"><font size="-1">' . format_bytesize($sum['used']) . '</font></td>'
           . '<td align="right" valign="top"><font size="-1">' . format_bytesize($sum['size']) . '</font></td></tr>'
           . '</table>';

    return $_text;
}
?>
