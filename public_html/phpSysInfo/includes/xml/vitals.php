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
// xml_vitals()
//
function xml_vitals () {
    global $sysinfo;
    $ar_buf = $sysinfo->loadavg(); 

    for ($i=0;$i<3;$i++) {
        if ($ar_buf[$i] > 2) {
            $load_avg .= ' ';
        } else {
            $load_avg .= $ar_buf[$i] . ' ';
        }
    }

    $_text = "  <Vitals>\n"
           . "    <Hostname>" . $sysinfo->chostname() . "</Hostname>\n"
           . "    <IPAddr>" . $sysinfo->ip_addr() . "</IPAddr>\n"
           . "    <Kernel>" . $sysinfo->kernel() . "</Kernel>\n"
           . "    <Uptime>" . $sysinfo->uptime() . "</Uptime>\n"
           . "    <Users>" . $sysinfo->users() . "</Users>\n"
           . "    <LoadAvg>" . trim($load_avg) . "</LoadAvg>\n"
           . "  </Vitals>\n";
    return $_text;
}

//
// html_vitals()
//
function html_vitals () {
    global $XPath;
    global $text;

    $_text = '<table border="0" width="90%" align="center">'
           . '<tr><td valign="top"><font size="-1">'. $text['hostname'] .'</font></td><td><font size="-1">' . $XPath->getData('/phpsysinfo/Vitals/Hostname') . '</font></td></tr>'
           . '<tr><td valign="top"><font size="-1">'. $text['ip'] .'</font></td><td><font size="-1">' . $XPath->getData('/phpsysinfo/Vitals/IPAddr') . '</font></td></tr>'

           . '<tr><td valign="top"><font size="-1">'. $text['kversion'] .'</font></td><td><font size="-1">' . $XPath->getData('/phpsysinfo/Vitals/Kernel') . '</font></td></tr>'
           . '<tr><td valign="top"><font size="-1">'. $text['uptime'] .'</font></td><td><font size="-1">' . $XPath->getData('/phpsysinfo/Vitals/Uptime') . '</font></td></tr>'
           . '<tr><td valign="top"><font size="-1">'. $text['users'] .'</font></td><td><font size="-1">' . $XPath->getData('/phpsysinfo/Vitals/Users') . '</font></td></tr>'
           . '<tr><td valign="top"><font size="-1">'. $text['loadavg'] .'</font></td><td><font size="-1">' . $XPath->getData('/phpsysinfo/Vitals/LoadAvg') . '</font></td></tr>'
           . '</table>';

    return $_text;
}

?>
