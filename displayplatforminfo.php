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

require './pagegenerator.php';
require './database/database.class.php';
require './includes/functions.php';
require './includes/displayutils.php';
require './includes/chart.php';

$name = null;
if (isset($_GET['name'])) {
	$name = GET_sanitized('name');
}

// Check if device platform info is present and to what extension it belongs to
$extension = null;
DB::connect();
$result = DB::$connection->prepare("SELECT * from deviceplatforminfo d where name = :name order by reportid limit 1");
$result->execute(["name" => $name]);
DB::disconnect();
if ($result->rowCount() == 0) {
	PageGenerator::errorMessage("<strong>This is not the <strike>droid</strike> platform info you are looking for!</strong><br><br>You may have passed a wrong device limit name.");
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

<div class='header'>
	<h4 class='headercaption'><?= $caption; ?></h4>
</div>

<center>

	<?php 
		PageGenerator::platformNavigation("displayplatforminfo.php?name=$name", $platform, true);

		$display_utils = new DisplayUtils();

		// Gather data		
		$labels = [];
		$counts = [];
		DB::connect();
		$result = DB::$connection->prepare("SELECT value, count(0) as reports from deviceplatforminfo where name = :name $filter group by 1 order by 2 desc");
		$result->execute(['name' => $name]);
		$rows = $result->fetchAll(PDO::FETCH_ASSOC);
		foreach ($rows as $device_info) {
			$labels[] = $display_utils->getDisplayValue($name, $device_info['value']);
			$counts[] = $device_info['reports'];
		}
		DB::disconnect();
	?>

	<div class='chart-div'>
		<div id="chart"></div>
		<div class='valuelisting'>
			<table id="platforminfo" class="table table-striped table-bordered table-hover">
				<thead>
					<tr>
						<th>Value</th>
						<th>Reports</th>
					</tr>
				</thead>
				<tbody>
					<?php
					for ($i = 0; $i < count($labels); $i++) {
						$color_style = "style='border-left: ".Chart::getColor($i)." 3px solid'";
						$link = "listreports.php?platforminfo=$name&value=".$labels[$i].($platform ? "&platform=$platform" : "");
						echo "<tr>";
						echo "<td $color_style>".$labels[$i]."</td>";
						echo "<td><a href='$link'>".$counts[$i]."</a></td>";
						echo "</tr>";
					}
					?>
				</tbody>
			</table>

		</div>
	</div>
</center>

<script type="text/javascript">
	$(document).ready(function() {
		var table = $('#platforminfo').DataTable({
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
	<?php			
		Chart::draw($labels, $counts);
	?>
</script>

<?php PageGenerator::footer(); ?>

</body>
</html>