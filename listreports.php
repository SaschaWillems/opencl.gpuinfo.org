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

include 'pagegenerator.php';
include './includes/functions.php';
include './database/database.class.php';

class ListReports {

	public const DeviceInfo = 0;
	public const PlatformInfo = 1;

	public $filters = [];

	function addFilter($name) {
		$value = GET_sanitized($name);
		if (($value !== null) && (trim($value) != '')) {
			$this->filters[$name] = $value;
		}
	}

	function getFilter($name) {
		if (key_exists($name, $this->filters)) {
			$value = $this->filters[$name];
			if (trim($value) != '') {
				return $value;
			}
		}
		return null;
	}

	function hasFilter($name) {
		return (key_exists($name, $this->filters));
	}

	function hasFilters() {
		return (count($this->filters) > 0);
	}

	function belongsToExtension($name, $target, &$ext) {
		$table = null;
		switch ($target) {
			case self::DeviceInfo:
				$table = 'deviceinfo';
				break;
			case self::PlatformInfo:
				$table = 'deviceplatforminfo';
				break;
		}
		$res = false;
		if ($table) {
			DB::connect();
			$stmnt = DB::$connection->prepare("SELECT extension FROM $table where name = :name");
			$stmnt->execute([':name' => $name]);
			$row = $stmnt->fetch(PDO::FETCH_ASSOC);
			if ($row) {
				$ext = $row['extension'];
				$res = true;
			}
			DB::disconnect();
		}
		return $res;
	}
}

$list_reports = new ListReports();

$pageTitle = null;
$caption = null;
$subcaption = null;
$showTabs = true;
$filters = ['platform', 'extension', 'submitter', 'devicename', 'platformname', 'platformextension', 'extension', 'deviceinfo', 'platforminfo', 'value', 'invert'];
foreach ($filters as $filter) {
	$list_reports->addFilter($filter);
}
$inverted = $list_reports->hasFilter('invert') && ($list_reports->getFilter('invert') == true);

if ($list_reports->hasFilter('extension')) {
	$caption = "Reports ".($inverted ? "<b>not</b>" : "")." supporting device extension <code>".$list_reports->getFilter('extension')."</code>";
}
if ($list_reports->hasFilter('submitter')) {
	$caption = "Reports submitted by <code>".$list_reports->getFilter('submitter')."</code>";
}
if ($list_reports->hasFilter('devicename')) {
	$caption = "Reports for <code>".$list_reports->getFilter('devicename')."</code>";
}
if ($list_reports->hasFilter('platformname')) {
	$caption = "Reports for platform <code>".$list_reports->getFilter('platformname')."</code>";
}
if ($list_reports->hasFilter('platformextension')) {
	$caption = "Reports " . ($inverted ? "<b>not</b>" : "") . " supporting platform extension <code>".$list_reports->getFilter('platformextension')."</code>";
}
if ($list_reports->hasFilter('deviceinfo') && $list_reports->hasFilter('value')) {
	// @todo: getdisplayvalue?
	$caption = "Reports with <code>".$list_reports->getFilter('deviceinfo')."</code> = ".$list_reports->getFilter('value');
	$extension = null;
	if ($list_reports->belongsToExtension($list_reports->getFilter('deviceinfo'), $list_reports::DeviceInfo, $extension)) {
		$subcaption = "Part of the <code>$extension</code> extension";
	}
}
if ($list_reports->hasFilter('platforminfo') && $list_reports->hasFilter('value')) {
	// @todo: getdisplayvalue?
	$caption = "Reports with <code>".$list_reports->getFilter('platforminfo')."</code> = ".$list_reports->getFilter('value');
	$extension = null;
	if ($list_reports->belongsToExtension($list_reports->getFilter('platforminfo'), $list_reports::PlatformInfo, $extension)) {
		$subcaption = "Part of the <code>$extension</code> extension";
	}
}
$defaultHeader = !($list_reports->hasFilters());

// Platform (os)
$platform = 'all';
if ($list_reports->hasFilter('platform')) {
	$platform = $list_reports->getFilter('platform');
}
if ($platform && $platform !== 'all') {
	if (!$caption) {
		$caption = "Listing reports";
	}
	$caption .= " on <img src='images/".$platform."logo.png' class='platform-icon'/>".ucfirst($platform);
	$defaultHeader = false;
}
if ($subcaption) {
	$caption .= "<br/><br/>$subcaption";
}

PageGenerator::header($pageTitle == null ? "Reports" : "Reports for $pageTitle");

if ($defaultHeader) {
	echo "<div class='header'>";
	echo "	<h4>Listing reports</h4>";
	echo "</div>";
}
?>

<center>
	<?php
	if (!$defaultHeader) {
		echo "<div class='header'><h4>$caption</h4></div>";
	}

	if ($showTabs) {
		PageGenerator::platformNavigation('listreports.php', $platform, true, $list_reports->filters);
	}
	?>
	<div class='tablediv tab-content' style='display: inline-flex;'>
		<form method="get" action="comparereports.php">
			<table id='reports' class='table table-striped table-bordered table-hover responsive' style='width:auto'>
				<thead>
					<tr>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
						<th></th>
					</tr>
					<tr>
						<th>id</th>
						<th>Device</th>
						<th>Version</th>
						<th>Driver</th>
						<th>CL</th>
						<th>Type</th>
						<th>OS</th>
						<th>Version</th>
						<th>Platform</th>
						<th><input type='submit' value='Compare' class='button'></th>
					</tr>
				</thead>
			</table>
			<div id="errordiv" style="color:#D8000C;"></div>
		</form>
	</div>
</center>

<script>
	$(document).on("keypress", "form", function(event) {
		return event.keyCode != 13;
	});

	$(document).ready(function() {

		var table = $('#reports').DataTable({
			"processing": true,
			"serverSide": true,
			"paging": true,
			"searching": true,
			"lengthChange": false,
			"dom": 'lrtip',
			"pageLength": 25,
			"order": [
				[0, 'desc']
			],
			"columnDefs": [{
				"searchable": false,
				"targets": [0, 9],
				"orderable": false,
				"targets": 9,
			}],
			"ajax": {
				url: "api/backend/reports.php",
				data: {
					"filter": {
					<?php
					foreach ($list_reports->filters as $filter => $value) {
						echo "'$filter': '$value',".PHP_EOL;
					}
					?>
					}
				},
				error: function(xhr, error, thrown) {
					$('#errordiv').html('Could not fetch data (' + error + ')');
					$('#reports_processing').hide();
				}
			},
			"columns": [
				{
					data: 'id'
				},
				{
					data: 'devicename'
				},
				{
					data: 'deviceversion'
				},
				{
					data: 'driverversion'
				},
				{
					data: 'openclversion'
				},
				{
					data: 'devicetype'
				},
				{
					data: 'osname'
				},
				{
					data: 'osversion'
				},
				{
					data: 'osarchitecture'
				},
				{
					data: 'compare'
				},
			],
			// Pass order by column information to server side script
			fnServerParams: function(data) {
				data['order'].forEach(function(items, index) {
					data['order'][index]['column'] = data['columns'][items.column]['data'];
				});
			},
		});

		yadcf.init(table, [
			{
				column_number: 1,
				filter_type: "text",
				filter_delay: 500,
				style_class: "filter-240"
			},
			{
				column_number: 2,
				filter_type: "text",
				filter_delay: 500
			},
			{
				column_number: 3,
				filter_type: "text",
				filter_delay: 500
			},
			{
				column_number: 4,
				filter_type: "text",
				filter_delay: 500
			},
			{
				column_number: 5,
				filter_type: "text",
				filter_delay: 500
			},
			{
				column_number: 6,
				filter_type: "text",
				filter_delay: 500
			},
			{
				column_number: 7,
				filter_type: "text",
				filter_delay: 500
			},
			{
				column_number: 8,
				filter_type: "text",
				filter_delay: 500
			},
		], {
			filters_tr_index: 0
		});

	});
</script>

<?php PageGenerator::footer(); ?>

</body>

</html>