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
		$filter = "and reportid in (select id from reports where ostype = '$ostype')";
		$caption .= " on <img src='images/".$platform."logo.png' class='platform-icon'/>" . ucfirst($platform);
	} else {
		$platform = 'all';
	}
}
if ($extension) {
	$caption .= "<br><br>Part of the <code>$extension</code> extension";
}
?>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script>
	$(document).ready(function() {
		var table = $('#extensions').DataTable({
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

	<?php PageGenerator::platformNavigation("displaydeviceinfo.php?name=$name", $platform, true); ?>

	<div class='parentdiv'>
		<div id="chart"></div>
		<div class='valuelisting'>
			<table id="extensions" class="table table-striped table-bordered table-hover">
				<thead>
					<tr>
						<th>Value</th>
						<th>Reports</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$display_utils = new DisplayUtils();
					DB::connect();
					$result = DB::$connection->prepare("SELECT value, count(0) as reports from deviceinfo where name = :name $filter group by 1 order by 1");
					$result->execute(['name' => $name]);
					$rows = $result->fetchAll(PDO::FETCH_ASSOC);
					foreach ($rows as $device_info) {
						$link = "listreports.php?deviceinfo=$name&value=".$device_info["value"].($platform ? "&platform=$platform" : "");
						echo "<tr>";
						echo "<td>".$display_utils->getDisplayValue($name, $device_info['value'])."</td>";
						echo "<td><a href='$link'>".$device_info['reports']."</a></td>";
						echo "</tr>";
					}
					DB::disconnect();
					?>
				</tbody>
			</table>

		</div>
	</div>
</center>

<script type="text/javascript">
	google.charts.load('current', {
		'packages': ['corechart']
	});
	google.charts.setOnLoadCallback(drawChart);

	function drawChart() {

		var data = google.visualization.arrayToDataTable([
			['Value', 'Reports'],
			<?php
			$display_utils->display_all_flags = false;
			DB::connect();
			$result = DB::$connection->prepare("SELECT value, count(0) as reports from deviceinfo where name = :name $filter group by 1 order by 2 desc");
			$result->execute(['name' => $name]);
			$rows = $result->fetchAll(PDO::FETCH_ASSOC);
			foreach ($rows as $device_info) {
				echo "['" . $display_utils->getDisplayValue($name, $device_info['value']) . "'," . $device_info['reports'] . "],";
			}
			DB::disconnect();
			?>
		]);

		var options = {
			legend: {
				position: 'bottom'
			},
			chartArea: {
				width: "80%",
				height: "80%"
			},
			height: 360,
			width: 360
		};

		var chart = new google.visualization.PieChart(document.getElementById('chart'));

		chart.draw(data, options);
	}
</script>

<?php PageGenerator::footer(); ?>

</body>
</html>