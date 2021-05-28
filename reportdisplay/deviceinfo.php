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
?>

<table id='table_deviceinfo' class='table table-striped table-bordered table-hover responsive' style='width:100%;'>
	<thead>
		<tr>
			<td class='caption'>Property</td>
			<td class='caption'>Value</td>
		</tr>
	</thead>
	<tbody>
		<?php

		// @todo: display os info
		$device_info_field_aliases = [
			'osname' => 'Name',
			'osarchitecture' => 'Architecture',
			'osversion' => 'Version'
		];

		try {
			$device_info = $report->fetchDeviceInfo();
			$device_info_details = $report->fetchDeviceInfoDetails();
			// @todo: display mapping
			foreach ($device_info as $row) {
				$key = $row['name'];
				$valueHint = null;
				$value = $row['value'];
				$displayvalue = getDisplayValue($key, $value);
				// Shorten lengthy values and display them as hints
				$maxDisplayLength = 40;
				if (strlen($displayvalue) > $maxDisplayLength) {
					$valueHint = $value;
					$displayvalue = shorten($displayvalue, $maxDisplayLength);
				}
				if (array_key_exists($key, $device_info_field_aliases)) {
					$key = $device_info_field_aliases[$key];
				}
				echo "<tr>";
				$details = [];
				if (count($device_info_details) > 0 ){
					foreach($device_info_details as $info_detail) {
						if (strcasecmp($info_detail['deviceinfo'], $key) == 0) {
							$details[] = ['name' => $info_detail['name'], 'value' => $info_detail['value']];
						}
					}
				}
				echo "<td>$key</td>";
				if (count($details) > 0) {
					echo "<td>";
					foreach($details as $detail) {
						$detail_display_value = getDetailDisplayValue($key, $detail['name'], $detail['value']);
						echo $detail_display_value."<br/>";
					}
					echo "</td>";
				} else {
					if ($valueHint) {
						echo "<td><abbr title='$valueHint'>$displayvalue</abbr></td>";
					} else {
						echo "<td>$displayvalue</td>";
					}
				}
				echo "</tr>";
			}
			// @todo: OS info

		} catch (Exception $e) {
			die('Error while fetching report properties');
			DB::disconnect();
		}
		?>
	</tbody>
</table>