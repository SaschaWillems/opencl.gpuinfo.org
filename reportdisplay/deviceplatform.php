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

$platform_info = $report->fetchPlatformInfo();
$platform_extensions = $report->fetchPlatformExtensions();
?>

<div>
	<ul class='nav nav-tabs nav-level1'>
		<li class='active'><a data-toggle='tab' href='#platforminfo'>Platform info</a></li>
		<li><a data-toggle='tab' href='#platformextensions'>Platform extensions</a></li>
	</ul>
</div>

<div class="tab-content">

	<div id='platforminfo' class='tab-pane fade in active nesteddiv'>
		<table id='table_deviceplatforminfo' class='table table-striped table-bordered table-hover'>
			<thead>
				<tr>
					<td class='caption'>Property</td>
					<td class='caption'>Value</td>
				</tr>
			</thead>
			<tbody>
				<?php
				// @todo: display mapping
				foreach ($platform_info as $info) {
					$key = $info['name'];
					$valueHint = null;
					$value = $info['value'];
					// Shorten lengthy values and display them as hints
					$maxDisplayLength = 35;
					if (strlen($value) > $maxDisplayLength) {
						$valueHint = $value;
						$value = shorten($value, $maxDisplayLength);
					}
					echo "<tr>";
					echo "<td>$key</td>";
					if ($valueHint) {
						echo "<td><abbr title='$valueHint'>$value</abbr></td>";
					} else {
						echo "<td>$value</td>";

					}
					echo "</tr>";
				}
				?>
			</tbody>
		</table>
	</div>

	<div id='platformextensions' class='tab-pane nesteddiv'>
		<table id='table_deviceplatformextensions' class='table table-striped table-bordered table-hover'>
			<thead>
				<tr>
					<td class='caption'>Extension</td>
					<td class='caption'>Version</td>
				</tr>
			</thead>
			<tbody>
				<?php
				if ($platform_extensions) {
					foreach ($platform_extensions as $extension) {
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
	</div>

</diV>