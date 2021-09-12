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
$device_info_detail_report_data = [];
if (!$report_compare->fetchDeviceInfo($device_info_list, $device_info_report_data)) {
    PageGenerator::errorMessage("Error fetching data for report compare!");
}
if (!$report_compare->fetchDeviceInfoDetails($device_info_detail_report_data)) {
    PageGenerator::errorMessage("Error fetching data for report compare!");
}

$report_compare->beginTable('device-info-table');
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
 	echo "<td>".str_replace('CL_DEVICE_', '', $key)."</td>";
    for ($i = 0; $i < $report_compare->report_count; $i++) {
        echo "<td><div class='compare-info-value'>";
        if (key_exists($key, $device_info_report_data[$i])) {
            $has_detail = array_key_exists($key, $device_info_detail_report_data[$i]);
            if ($has_detail) {
                $details = $device_info_detail_report_data[$i][$key];
                foreach($details as $detail) {
                    $detail_display_value = $display_utils->getDetailDisplayValue($key, $detail['name'], $detail['detail'], $detail['value']);
                    echo $detail_display_value;
                }
            } else {
                $report_value = $device_info_report_data[$i][$key][0]['value'];
                $displayvalue = $display_utils->getDisplayValue($key, $report_value);
				// Shorten lengthy single line values and display them as hints
                $valueHint = null;
				if ((strlen($displayvalue) > 30) && (strpos($displayvalue, '<br/>') === false)) {
					$valueHint = $displayvalue;
					$displayvalue = shorten($displayvalue, 30);
				}
                if ($valueHint) {
                    echo "<abbr title='$valueHint'>$displayvalue</abbr>";
                } else {
                    echo $displayvalue;
                }                
            }
        } else {
            echo "<span class='na'>n/a</span>";
        }
        echo "</div></td>";
	}
    echo "</tr>";
}

$report_compare->endTable();