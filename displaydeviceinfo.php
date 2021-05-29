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

require './pagegenerator.php';
require './database/database.class.php';
require './includes/functions.php';
require './includes/displayutils.php';

$name = null;
if (isset($_GET['name'])) {
	$name = GET_sanitized('name');
}

// @todo: replace with function
$os = null;
$filter = null;
if (isset($_GET['os'])) {
	$os = GET_sanitized($_GET['os']);
	if (!in_array($os, ['windows', 'android', 'linux', 'ios', 'osx'])) {
		$os = null;
	}
	if ($os) {
		if (in_array($os, ['windows', 'android', 'ios', 'osx'])) {
			$filter = "where reportid in (select id from reports where osname = '$os')";
		}
		if (in_array($os, ['linux'])) {
			$filter = "where reportid in (select id from reports where osname not in ('windows', 'android', 'ios', 'osx'))";
		}
	}
}

// Check if capability is present
DB::connect();
$result = DB::$connection->prepare("SELECT * from deviceinfo d where name = :name order by reportid limit 1");
$result->execute(["name" => $name]);
DB::disconnect();
if ($result->rowCount() == 0) {
	PageGenerator::errorMessage("<strong>This is not the <strike>droid</strike> device info you are looking for!</strong><br><br>You may have passed a wrong device limit name.");
}

PageGenerator::header($name);

$caption = "Value distribution for <code>$name</code>";

// @todo: replace with function
$platform = null;
if (isset($_GET['platform'])) {
	$platform = $_GET["platform"];
	if ($platform !== "all") {
		switch ($platform) {
			case 'windows':
				$ostype = 0;
				break;
			case 'linux':
				$ostype = 1;
				break;
			case 'android':
				$ostype = 2;
				break;
		}
		$filter .= "and reportid in (select id from reports where ostype = '" . $ostype . "')";
		$caption .= " on <img src='images/" . $platform . "logo.png' height='14px' style='padding-right:5px'/>" . ucfirst($platform);
	}
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
					DB::connect();
					$result = DB::$connection->prepare("SELECT value, count(0) as reports from deviceinfo where name = :name $filter group by 1 order by 1");
					$result->execute(['name' => $name]);
					$rows = $result->fetchAll(PDO::FETCH_ASSOC);
					foreach ($rows as $cap) {
						$link = "listreports.php?deviceinfo=$name&value=" . $cap["value"] . ($platform ? "&platform=$platform" : "");
						echo "<tr>";
						echo "<td>" . $cap["value"] . "</td>";
						echo "<td><a href='$link'>" . $cap["reports"] . "</a></td>";
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
			DB::connect();
			$result = DB::$connection->prepare("SELECT value, count(0) as reports from deviceinfo where name = :name $filter group by 1 order by 2 desc");
			$result->execute(['name' => $name]);
			$rows = $result->fetchAll(PDO::FETCH_ASSOC);
			foreach ($rows as $row) {
				echo "['" . $row['value'] . "'," . $row['reports'] . "],";
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