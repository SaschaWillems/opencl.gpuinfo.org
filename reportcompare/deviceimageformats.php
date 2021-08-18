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
$device_formats_list = [];
$device_formats_report_data = [];
if (!$report_compare->fetchDeviceImageformats($device_formats_list, $device_formats_report_data)) {
    PageGenerator::errorMessage("Error fetching data for report compare!");
}

$report_compare->beginTable('device-info-imageformats');
?>

<table id='device-image-formats' class='table table-striped table-bordered table-hover'>
    <thead>
		<tr>        
			<th></th>
			<th></th>
            <th>Device</th>
			<th><abbr title="CL_MEM_READ_WRITE">RW</abbr></th>
			<th><abbr title="CL_MEM_WRITE_ONLY">WO</abbr></th>
			<th><abbr title="CL_MEM_READ_ONLY">RO</abbr></th>
			<th><abbr title="CL_MEM_KERNEL_READ_AND_WRITE">KRW</abbr></th>
		</tr>
	</thead>

<?php
    foreach ($device_formats_list as $image_format) {
        $image_format_infos = [];
        // Get image format info for the selected reports (can be null for reports without support for a single format)
        foreach ($device_formats_report_data as $index => $report_data) {
            $image_format_infos[] = $report_compare->getImageFormatInfo($report_data, $image_format);
        }
        // Check if image format support differs among selected reports
        $differing_values = false;
        $last_value = $image_format_infos[0] ? $image_format_infos[0][0]['flags'] : null;
        for ($i = 1; $i < $report_compare->report_count; $i++) {
            $curr_value = $image_format_infos[$i] ? $image_format_infos[$i][0]['flags'] : null;
            if ($curr_value != $last_value) {
                $differing_values = true;
                break;
            }
        }
        foreach ($device_formats_report_data as $index => $report_data) {
            $image_format_info = $image_format_infos[$index];
            echo $differing_values ? "<tr>" : "<tr class='same'>";
            echo "<td>";
            echo "Format ".$display_utils->displayMemObjectType($image_format['type'])." with channel order ";
            echo $display_utils->displayChannelOrder($image_format['channelorder']);
            echo "</td>";
            $css_class = $image_format_info ? null : 'class="na"';
            echo "<td $css_class>".$display_utils->displayChannelType($image_format['channeltype'])."</td>";
            echo "<td $css_class>".$report_compare->device_infos[$index]->device_description." - ".$report_compare->device_infos[$index]->driver_version."</td>";

            if ($image_format_info) {
                $cl_mem_flags = [
                    (1 << 0),  // CL_MEM_READ_WRITE
                    (1 << 1),  // CL_MEM_WRITE_ONLY                           
                    (1 << 2),  // CL_MEM_READ_ONLY
                    (1 << 12), // CL_MEM_KERNEL_READ_AND_WRITE
                ];                
				foreach ($cl_mem_flags as $flag) {
					$icon = ($flag & $image_format_info[0]['flags']) ? 'check' : 'missing';
					echo "<td class='format-support-cell'><img src='images/icons/$icon.png' class='checkmark'></td>";
				}                
            } else {
                for ($i = 0; $i < 4; $i++) {
                    echo "<td class='format-support-cell'><img src='images/icons/unsupported.png' class='checkmark'></td>";
                }
            }
            echo "</tr>";
        }
    }
?>

</table>