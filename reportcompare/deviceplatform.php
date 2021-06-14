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
// todo
?>

<div>
	<ul class='nav nav-tabs'>
		<li class='active'><a data-toggle='tab' href='#platform-info'>Platform info</a></li>
		<li><a data-toggle='tab' href='#platform-extensions'>Platform extensions</a></li>
	</ul>
</div>
<div class='tab-content'>

<?php
// Platform info
$report_compare->beginTab('platform-info', true);
$report_compare->beginTable('platform-info-table');
$report_compare->insertTableHeader('', false, true);
try {
	$platform_info_list = [];
	$platform_info_report_data = [];
	$report_compare->fetchPlatformInfo($platform_info_list, $platform_info_report_data);

	foreach ($platform_info_list as $platform_info) {
		$key = $platform_info['name'];
		// Check if values differ among the selected reports
		$differing_values = false;
		$last_value = null;
		if (key_exists($key, $platform_info_report_data[0])) {
			$last_value = $platform_info_report_data[0][$key][0];
		}
		for ($i = 1; $i < $report_compare->report_count; $i++) {
			if (key_exists($key, $platform_info_report_data[$i])) {
				if ($platform_info_report_data[$i][$key][0] != $last_value) {
					$differing_values = true;
					break;
				}
			}
		}
		// Display values
		echo $differing_values ? "<tr>" : "<tr class='same'>";
		 echo "<td>".str_replace('CL_PLATFORM_', '', $key)."</td>";
		for ($i = 0; $i < $report_compare->report_count; $i++) {
			echo "<td><div class='compare-info-value'>";
			if (key_exists($key, $platform_info_report_data[$i])) {
				$report_value = $platform_info_report_data[$i][$key][0]['value'];
				$displayvalue = $display_utils->getDisplayValue($key, $report_value);
				echo $displayvalue;
			} else {
				echo "<span class='na'>n/a</span>";
			}
			echo "</div></td>";
		}
		echo "</tr>";
	}	
} catch (Exception $e) {
	echo "Error fetching data";
}
$report_compare->endTable();
$report_compare->endTab();

// Platform extensions
$report_compare->beginTab('platform-extensions', false);
$report_compare->beginTable('platform-extensions-table');
$report_compare->insertTableHeader('', false, true);
try {
	$platform_extensions_list = [];
	$platform_extensions_report_data = [];
	$report_compare->fetchPlatformExtensions($platform_extensions_list, $platform_extensions_report_data);
	foreach ($platform_extensions_list as $platform_extension) {
		$ext = $platform_extension['name'];
		if (trim($ext) == "") {
			continue;
		}
		// Check if extension support differs among selected reports
		$differing_values = false;
		$last_value = key_exists($ext, $platform_extensions_report_data[0]);
		for ($i = 1; $i < $report_compare->report_count; $i++) {
			$curr_value = key_exists($ext, $platform_extensions_report_data[$i]);
			if ($curr_value != $last_value) {
				$differing_values = true;
				break;
			}
		}
		// Display values
		echo $differing_values ? "<tr>" : "<tr class='same'>";
		 echo "<td>$ext</td>";
		for ($i = 0; $i < $report_compare->report_count; $i++) {
			echo "<td>";
			if (key_exists($ext, $platform_extensions_report_data[$i])) {
				echo "<img src='images/icons/check.png' class='checkmark'>";
			} else {
				echo "<img src='images/icons/missing.png' class='checkmark'>";
			}
			echo "</td>";
		}
		echo "</tr>";
	}	
} catch (Exception $e) {
	echo "Error fetching data";
}

$report_compare->endTable();
$report_compare->endTab();
?>

</div>