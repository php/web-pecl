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
// xml_hardware()
//
function xml_hardware () {
    global $sysinfo;
    global $text;

    $sys = $sysinfo->cpu_info();

    $ar_buf = $sysinfo->pci(); 

    if (count($ar_buf)) {
        for ($i=0;$i<sizeof($ar_buf);$i++) {
            if ($ar_buf[$i]) {
                $pci_devices .= "      <Device>" . chop($ar_buf[$i]) . "</Device>\n";
            }
        }
    }

    $ar_buf = $sysinfo->ide(); 

    ksort($ar_buf);

    if (count($ar_buf)) {
        while (list($key, $value) = each($ar_buf)) {
            $ide_devices .= "      <Device>" . $key . ': ' . $ar_buf[$key]['model'];
            if (isset($ar_buf[$key]['capacity'])) {
                $ide_devices .= ' (' . $text['capacity'] . ': ' . format_bytesize($ar_buf[$key]['capacity']/2).')';
            }
            $ide_devices .= "</Device>\n";
        }
    }

    $ar_buf = $sysinfo->scsi(); 

    if (count($ar_buf)) {
        for ($i=0;$i<sizeof($ar_buf);$i++) {
            $scsi_devices .= "      <Device>" . $ar_buf[$i] . "</Device>\n";
        }
    }

    $_text = "  <Hardware>\n";
    $_text .= "    <CPU>\n";
    if ($sys['cpus']) {
        $_text .= "      <Number>" . $sys['cpus'] . "</Number>\n";
    }
    if ($sys['model']) {
        $_text .= "      <Model>" . $sys['model'] . "</Model>\n";
    }
    if ($sys['mhz']) {
        $_text .= "      <Mhz>" . $sys['mhz'] . "</Mhz>\n";
    }
    if ($sys['cache']) {
        $_text .= "      <Cache>" . $sys['cache'] . "</Cache>\n";
    }
    if ($sys['bogomips']) {
        $_text .= "      <Bogomips>" . $sys['bogomips'] . "</Bogomips>\n";
    }
    $_text .= "    </CPU>\n";


    $_text .= "    <PCI>\n";
    if ($pci_devices) {
        $_text .= $pci_devices;
    }
    $_text .= "    </PCI>\n";


    $_text .= "    <IDE>\n";
    if ($ide_devices) {
        $_text.= $ide_devices;
    }
    $_text .= "    </IDE>\n";

    $_text .= "    <SCSI>\n";
    if ($scsi_devices) {
        $_text .= $scsi_devices;
    }
    $_text .= "    </SCSI>\n";
    $_text .= "  </Hardware>\n";

    return $_text;
}


//
// html_hardware()
//
function html_hardware () {
    global $XPath;
    global $text;

    for ($i=1; $i<sizeof($XPath->getDataParts('/phpsysinfo/Hardware/PCI')); $i++) {
        if ($XPath->match("/phpsysinfo/Hardware/PCI/Device[$i]")) {
            $pci_devices .= $XPath->getData("/phpsysinfo/Hardware/PCI/Device[$i]") . '<br>';
        }
    }

    for ($i=1; $i<sizeof($XPath->getDataParts('/phpsysinfo/Hardware/IDE')); $i++) {
        if ($XPath->match("/phpsysinfo/Hardware/IDE/Device[$i]")) {
            $ide_devices .= $XPath->getData("/phpsysinfo/Hardware/IDE/Device[$i]") . '<br>';
        }
    }

    for ($i=1; $i<sizeof($XPath->getDataParts('/phpsysinfo/Hardware/SCSI')); $i++) {
        if ($XPath->match("/phpsysinfo/Hardware/SCSI/Device[$i]")) {
            $scsi_devices .= $XPath->getData("/phpsysinfo/Hardware/SCSI/Device[$i]") . '<br>';
        }
    }

    $_text = '<table border="0" width="90%" align="center">';

    if ($XPath->match("/phpsysinfo/Hardware/CPU/Number")) {
        $_text .= '<tr><td valign="top"><font size="-1">'. $text['numcpu'] .'</font></td><td><font size="-1">' . $XPath->getData("/phpsysinfo/Hardware/CPU/Number") . '</font></td></tr>';
    }
    if ($XPath->match("/phpsysinfo/Hardware/CPU/Model")) {
        $_text .= '<tr><td valign="top"><font size="-1">'. $text['cpumodel'] .'</font></td><td><font size="-1">' . $XPath->getData("/phpsysinfo/Hardware/CPU/Model") . '</font></td></tr>';
    }
    if ($XPath->match("/phpsysinfo/Hardware/CPU/Mhz")) {
        $_text .= '<tr><td valign="top"><font size="-1">'. $text['mhz'] .'</font></td><td><font size="-1">' . $XPath->getData("/phpsysinfo/Hardware/CPU/Mhz") . '</font></td></tr>';
    }
    if ($XPath->match("/phpsysinfo/Hardware/CPU/Cache")) {
        $_text .= '<tr><td valign="top"><font size="-1">'. $text['cache'] .'</font></td><td><font size="-1">' . $XPath->getData("/phpsysinfo/Hardware/CPU/Cache") . '</font></td></tr>';
    }
    if ($XPath->match("/phpsysinfo/Hardware/CPU/Bogomips")) {
        $_text .= '<tr><td valign="top"><font size="-1">'. $text['bogomips'] .'</font></td><td><font size="-1">' . $XPath->getData("/phpsysinfo/Hardware/CPU/Bogomips") . '</font></td></tr>';
    }

    $_text .= '<tr><td valign="top"><font size="-1">'. $text['pci'] .'</font></td><td><font size="-1">';
    if ($pci_devices) {
        $_text .= $pci_devices;
    } else {
        $_text .= '<i>' . $text['none'] . '</i>';
    }
    $_text .= '</font></td></tr>';

    $_text .= '<tr><td valign="top"><font size="-1">'. $text['ide'] .'</font></td><td><font size="-1">';
    if ($ide_devices) {
        $_text .= $ide_devices;
    } else {
        $_text .= '<i>' . $text['none'] . '</i>';
    }
    $_text .= '</font></td></tr>';

    $_text .= '<tr><td valign="top"><font size="-1">'. $text['scsi'] .'</font></td><td><font size="-1">';
    if ($scsi_devices) {
        $_text .= $scsi_devices;
    } else {
        $_text .= '<i>' . $text['none'] . '</i>';
    }
    $_text .= '</font></td></tr>';
    $_text .= '</table>';

    return $_text;
}

?>
