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
// xml_network()
//
function xml_network () {
    global $sysinfo;
    $net = $sysinfo->network();

    $_text = "  <Network>\n";
    while (list($dev, $stats) = each($net)) {
        $_text .= "    <NetDevice>\n"
               .  "      <Name>" . trim($dev) . "</Name>\n"
               .  "      <RxBytes>" . $stats['rx_bytes'] . "</RxBytes>\n"
               .  "      <TxBytes>" . $stats['tx_bytes'] . "</TxBytes>\n"
               .  "      <Errors>" . $stats['errs'] . "</Errors>\n"
               .  "      <Drops>" . $stats['drop'] . "</Drops>\n"
               .  "    </NetDevice>\n";
    }
    $_text .= "  </Network>\n";

    return $_text;
}

//
// html_network()
//
function html_network () {
    global $XPath;
    global $text;

    $_text = '<table width="100%" align="center">'
           . '<tr><td align="left" valign="top"><font size="-1"><b>' . $text['device'] . '</b></font></td>'
           . '<td align="right" valign="top"><font size="-1"><b>' . $text['received'] . '</b></font></td>'
           . '<td align="right" valign="top"><font size="-1"><b>' . $text['sent'] . '</b></font></td>'
           . '<td align="right" valign="top"><font size="-1"><b>' . $text['errors'] . '</b></font></td>';

    for ($i=1; $i<sizeof($XPath->getDataParts('/phpsysinfo/Network')); $i++) {
        if ($XPath->match("/phpsysinfo/Network/NetDevice[$i]/Name")) {
            $_text .= "\t<tr>\n";
            $_text .= "\t\t<td align=\"left\" valign=\"top\"><font size=\"-1\">" . $XPath->getData("/phpsysinfo/Network/NetDevice[$i]/Name") . "</font></td>\n";
            $_text .= "\t\t<td align=\"right\" valign=\"top\"><font size=\"-1\">" . format_bytesize($XPath->getData("/phpsysinfo/Network/NetDevice[$i]/RxBytes") / 1024) . "</font></td>\n";
            $_text .= "\t\t<td align=\"right\" valign=\"top\"><font size=\"-1\">" . format_bytesize($XPath->getData("/phpsysinfo/Network/NetDevice[$i]/TxBytes") / 1024) . "</font></td>\n";
            $_text .= "\t\t<td align=\"right\" valign=\"top\"><font size=\"-1\">" . $XPath->getData("/phpsysinfo/Network/NetDevice[$i]/Errors") . '/' . $XPath->getData("/phpsysinfo/Network/NetDevice[$i]/Drops") . "</font></td>\n";
            $_text .= "\t</tr>\n";
        }

    }
    $_text .= '</table>';

    return $_text;
}

?>
