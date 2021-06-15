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
// $report_compare->insertTableHeader('', false, true);

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
        foreach ($device_formats_report_data as $index => $report_data) {
            $image_format_info = $report_compare->getImageFormatInfo($report_data, $image_format);
            echo "<tr>";
            echo "<td>";
            echo "Format ".$display_utils->displayMemObjectType($image_format['type'])." with channel order ";
            echo $display_utils->displayChannelOrder($image_format['channelorder']);
            echo "</td>";
            $css_class = $image_format_info ? null : 'class="na"';
            echo "<td $css_class>".$display_utils->displayChannelType($image_format['channeltype'])."</td>";
            echo "<td $css_class>".$report_compare->device_infos[$index]->device_description."</td>";

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