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
$device_info_list = [];
$device_info_report_data = [];
if (!$report_compare->fetchDeviceInfo($device_info_list, $device_info_report_data)) {
    PageGenerator::errorMessage("Error fetching data for report compare!");
}

$report_compare->beginTable('comparedevices');
$report_compare->insertTableHeader('', false, true);

foreach ($device_info_list as $device_info) {
    $key = $device_info['name'];
    // Check if values differ among the selected reports
    $differing_values = false;
    $last_value = null;
    if (key_exists($key, $device_info_report_data[0])) {
        $last_value = $device_info_report_data[0][$key][0];
    }
    for ($i = 1; $i < $report_compare->report_count; $i++) {
        if (key_exists($key, $device_info_report_data[$i])) {
            if ($device_info_report_data[$i][$key][0] != $last_value) {
                $differing_values = true;
                break;
            }
        }
    }
    // Display values
    echo $differing_values ? "<tr>" : "<tr class='same'>";
 	echo "<td>".$report_compare->insterDiffIcon($key, $differing_values)."</td>";
    for ($i = 0; $i < $report_compare->report_count; $i++) {
        echo "<td>";
        if (key_exists($key, $device_info_report_data[$i])) {
            $report_value = $device_info_report_data[$i][$key][0]['value'];
            $displayvalue = $display_utils->getDisplayValue($key, $report_value);
            // @todo: abbreviate long lines
            echo $displayvalue;
        } else {
            echo "<span class='na'>n/a</span>";
        }
        echo "</td>";
	}
    echo "</tr>";
}

$report_compare->endTable();