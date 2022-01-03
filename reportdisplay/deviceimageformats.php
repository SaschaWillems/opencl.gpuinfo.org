<?php

/** 		
 *
 * OpenCL hardware capability database server implementation
 *	
 * Copyright (C) 2021-2022 by Sascha Willems (www.saschawillems.de)
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
?>

<table id='table_deviceimageformats' class='table table-striped table-bordered table-hover'>
	<thead>
		<tr>
			<th>Format</th>
			<th>Channel order</th>
			<th>Channel type</th>
			<th><abbr title="CL_MEM_READ_WRITE">RW</abbr></th>
			<th><abbr title="CL_MEM_WRITE_ONLY">WO</abbr></th>
			<th><abbr title="CL_MEM_READ_ONLY">RO</abbr></th>
			<th><abbr title="CL_MEM_KERNEL_READ_AND_WRITE">KRW</abbr></th>
		</tr>
	</thead>
	<tbody>
		<?php
		$cl_mem_flags = ['CL_MEM_READ_WRITE', 'CL_MEM_WRITE_ONLY', 'CL_MEM_READ_ONLY', 'CL_MEM_KERNEL_READ_AND_WRITE'];
		$data = $report->fetchImageFormats();
		if ($data) {
			foreach ($data as $format) {				
				echo "<tr>";
				echo "<td>".$display_utils->displayMemObjectType($format['type'])."</td>";
				echo "<td>".$display_utils->displayChannelOrder($format['channelorder'])."</td>";
				echo "<td>".$display_utils->displayChannelType($format['channeltype'])."</td>";
				foreach ($cl_mem_flags as $flag) {
					$icon = $format[$flag] ? 'check' : 'missing';
					echo "<td><img src='images/icons/$icon.png' width=16px></td>";
				}
				echo "</tr>";
			}
		}
		?>
	</tbody>
</table>