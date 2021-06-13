<?php

/** 		
 *
 * OpenCL hardware capability database server implementation
 *	
 * Copyright (C) 2021 by Sascha Willems (www.saschawillems.de)
 *	
 * This code is free software, you can redistribute it and/or
 * modify it under the terms of the GNU Affero General Public
 * License version 3 as published by the Free Software Foundation.
 *	
 * Please review the following information to ensure the GNU Lesser
 * General Public License version 3 requirements will be met:
 * http://www.gnu.org/licenses/agpl-3.0.de.html
 *	
 * The code is distributed WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR
 * PURPOSE.  See the GNU AGPL 3.0 for more details.		
 *
 */

// Gather data
$device_extensions_list = [];
$device_extensions_report_data = [];
if (!$report_compare->fetchDeviceExtensions($device_extensions_list, $device_extensions_report_data)) {
    PageGenerator::errorMessage("Error fetching data for report compare!");
}

$report_compare->beginTable('compareextensions');
$report_compare->insertTableHeader('', false, true);

foreach ($device_extensions_list as $device_extension) {
    $ext = $device_extension['name'];
    if (trim($ext) == "") {
        continue;
    }
    // Check if extension support differs among selected reports
    $differing_values = false;
    $last_value = key_exists($ext, $device_extensions_report_data[0]);
    for ($i = 1; $i < $report_compare->report_count; $i++) {
        $curr_value = key_exists($ext, $device_extensions_report_data[$i]);
        if ($curr_value != $last_value) {
            $differing_values = true;
            break;
        }
    }
    // Display values
    echo $differing_values ? "<tr>" : "<tr class='same'>";
 	echo "<td>".$report_compare->insterDiffIcon($ext, $differing_values)."</td>";
    for ($i = 0; $i < $report_compare->report_count; $i++) {
        echo "<td>";
        if (key_exists($ext, $device_extensions_report_data[$i])) {
            echo "<img src='images/icons/check.png' class='checkmark'>";
        } else {
            echo "<img src='images/icons/missing.png' class='checkmark'>";
        }
        echo "</td>";
	}
    echo "</tr>";
}

$report_compare->endTable();