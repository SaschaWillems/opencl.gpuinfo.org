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

<table id='table_deviceextensions' class='table table-striped table-bordered table-hover'>
	<thead>
		<tr>
			<td class='caption'>Extension</td>
			<td class='caption'><abbr title="Only available with OpenCL 3.0 and newer">Version</abbr></td>
		</tr>
	</thead>
	<tbody>
		<?php
		$data = $report->fetchExtensions();
		if ($data) {
			foreach ($data as $extension) {
				if ($extension['version'] > 0) {
					$version = versionToString($extension['version']);
				} else {
					$version = "<span class='na'>n/a</span>";
				}
				echo "<tr>";
				echo "<td>".$extension['name']."</td>";
				echo "<td>$version</td>";
				echo "</tr>";
			}
		}
		?>
	</tbody>
</table>