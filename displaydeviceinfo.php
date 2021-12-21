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

 // @todo: adopt changes made on displayplatforminfo.php

require './pagegenerator.php';
require './database/database.class.php';
require './includes/functions.php';
require './includes/displayutils.php';
require './includes/chartutils.php';

$name = null;
if (isset($_GET['name'])) {
	$name = GET_sanitized('name');
}

// Check if device info is valid and to what extension it belongs to
$extension = null;
DB::connect();
$result = DB::$connection->prepare("SELECT * from deviceinfo d where name = :name order by reportid limit 1");
$result->execute(["name" => $name]);
DB::disconnect();
if ($result->rowCount() == 0) {
	PageGenerator::errorMessage("<strong>This is not the <strike>droid</strike> device info you are looking for!</strong><br><br>You may have passed a wrong device limit name.");
} else {
	$row = $result->fetch(PDO::FETCH_ASSOC);
	$extension = $row['extension'];
}

PageGenerator::header($name);

$caption = "Value distribution for <code>$name</code>";
$filter = null;
$platform = null;
if (isset($_GET['platform'])) {
	$platform = GET_sanitized('platform');
	if (($platform !== null) && ($platform !== "")) {
		$ostype = ostype($platform);
		$filter = "and di.reportid in (select id from reports where ostype = '$ostype')";
		$caption .= " on <img src='images/".$platform."logo.png' class='platform-icon'/>" . ucfirst($platform);
	} else {
		$platform = 'all';
	}
}
if ($extension) {
	$caption .= "<br><br>Part of the <code>$extension</code> extension";
}
?>

<script>
	$(document).ready(function() {
		var table = $('#deviceinfo').DataTable({
			"pageLength": -1,
			"paging": false,
			"stateSave": false,
			"searchHighlight": true,
			"dom": '',
			"bInfo": false,
			"order": [
				[0, "asc"]
			]
		});
	});
</script>

<div class='header'>
	<h4 class='headercaption'><?php echo $caption; ?></h4>
</div>

<center>

	<?php 
		PageGenerator::platformNavigation("displaydeviceinfo.php?name=$name", $platform, true); 

		$display_utils = new DisplayUtils();
		$display_utils->display_all_flags = false;

		// Gather data
		$labels = [];
		$counts = [];
		$values = [];

		DB::connect();
		// Check if values are stored in device info details (need to be handled different)
		// This is often the case with device info that also store versioning information (e.g. CL_DEVICE_ILS_WITH_VERSION) and requires access to an additional layer of data
		$values_from_details = (DB::getCount("SELECT count(0) from deviceinfodetails did left join deviceinfo di on did.deviceinfoid = di.id where di.name = :name $filter", ['name' => $name]) > 0);

		if ($values_from_details) {
			// If that's the case, we need to fetch unique value combinations from the deviceinfodetails 1:n relation
			$result = DB::$connection->prepare(
					"SELECT did.name, group_concat(did.value) as `values`, count(*) as reports 
					from deviceinfodetails did 
					left join deviceinfo di on did.deviceinfoid = di.id and did.reportid = di.reportid 
					where di.name = :name $filter 
					group by did.name, di.reportid");
			$result->execute(['name' => $name]);
			$rows = $result->fetchAll(PDO::FETCH_ASSOC);
			// Gather all unique value entry combinations
			$detail_info_detail_entries = [];
			foreach ($rows as $row) {
				// Primary key is a combo of name + aggregated values
				$id = $row['name'].'-'.$row['values'];
				if (!array_key_exists($id, $detail_info_detail_entries)) {
					// Push new entry
					$detail_info_detail_entries[$id] = [				
						'name' => $row['name'],
						'values' => $row['values'],
						'reports' => $row['reports']
					];
				} else {
					// Increase report count
					$detail_info_detail_entries[$id]['reports'] += $row['reports'];
				}				
			}
			// Aggregate
			foreach ($detail_info_detail_entries as $key => $entry) {
				$display_value = '';
				foreach (explode(',', $entry['values']) as $value) {					
					$display_value .= $display_utils->getDetailDisplayValue($name, $entry['name'], null, $value);
				}
				$labels[] = $display_value;
				$values[] = $entry['values'];
				$counts[] = $entry['reports'];
			}
		} else {
			DB::connect();
			$result = DB::$connection->prepare("SELECT value, count(0) as reports from deviceinfo di where name = :name $filter group by 1 order by 1");
			$result->execute(['name' => $name]);
			$rows = $result->fetchAll(PDO::FETCH_ASSOC);
			foreach ($rows as $device_info) {
				$labels[] = $display_utils->getDisplayValue($name, $device_info['value']);
				$values[] = $device_info['value'];
				$counts[] = $device_info['reports'];
			}
			DB::disconnect();			
		}
		DB::disconnect();	
	?>

	<div class='deviceinfodiv info-detail'>
		<div id="chart"></div>
		<div class='valuelisting'>
			<table id="deviceinfo" class="table table-striped table-bordered table-hover">
				<thead>
					<tr>
						<th>Value</th>
						<th>Reports</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$color_idx = 0;
					for ($i = 0; $i < count($labels); $i++) {
						$color_style = "style='border-left: ".$chart_colors[$color_idx]." 3px solid'";
						// @todo: separate link for detail info? (or maybe as an argument)
						$link = "listreports.php?deviceinfo=$name&value=".$values[$i].($platform ? "&platform=$platform" : "");
						echo "<tr>";
						echo "<td $color_style>".str_replace('\\n', '<br/>', $labels[$i])."</td>";
						echo "<td><a href='$link'>".$counts[$i]."</a></td>";
						echo "</tr>";
						$color_idx++;
						if ($color_idx > count($chart_colors)) {
							$color_idx = 0;
						}
					}
					?>
				</tbody>
			</table>

		</div>
	</div>
</center>

<script type="text/javascript">
	<?php			
		drawChart($labels, $counts, $chart_colors);
	?>
</script>

<?php PageGenerator::footer(); ?>

</body>
</html>