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

include 'pagegenerator.php';
include './includes/functions.php';
include './includes/filterlist.class.php';
include './includes/displayutils.php';
include './database/database.class.php';

$filters = ['platform', 'extension', 'submitter', 'devicename', 'platformname', 'platformextension', 'extension', 'deviceinfo', 'platforminfo', 'value', 'invert', 'detailvalue'];
$filter_list = new Filterlist($filters);

$pageTitle = null;
$caption = null;
$subcaption = null;
foreach ($filters as $filter) {
	$filter_list->addFilter($filter);
}
$inverted = $filter_list->hasFilter('invert') && ($filter_list->getFilter('invert') == true);

if ($filter_list->hasFilter('extension')) {
	$caption = "Reports ".($inverted ? "<b>not</b>" : "")." supporting device extension <code>".$filter_list->getFilter('extension')."</code>";
}
if ($filter_list->hasFilter('submitter')) {
	$caption = "Reports submitted by <code>".$filter_list->getFilter('submitter')."</code>";
}
if ($filter_list->hasFilter('devicename')) {
	$caption = "Reports for <code>".$filter_list->getFilter('devicename')."</code>";
}
if ($filter_list->hasFilter('platformname')) {
	$caption = "Reports for platform <code>".$filter_list->getFilter('platformname')."</code>";
}
if ($filter_list->hasFilter('platformextension')) {
	$caption = "Reports " . ($inverted ? "<b>not</b>" : "") . " supporting platform extension <code>".$filter_list->getFilter('platformextension')."</code>";
}
if ($filter_list->hasFilter('deviceinfo') && $filter_list->hasFilter('value')) {
	$display_utils = new DisplayUtils();
	$display_utils->display_all_flags = false;
	$info = $filter_list->getFilter('deviceinfo');
	$value = $filter_list->getFilter('value');
	$displayvalue = null;
	if ($filter_list->hasFilter('detailvalue')) {
		$displayvalue = $value;
	} else {
		$displayvalue = $display_utils->getDisplayValue($info, $value);
	}
	$caption = "Reports with <code>$info</code> = $displayvalue";
	$extension = null;
	if ($filter_list->belongsToExtension($filter_list->getFilter('deviceinfo'), $filter_list::DeviceInfo, $extension)) {
		$subcaption = "Part of the <code>$extension</code> extension";
	}
}
if ($filter_list->hasFilter('platforminfo') && $filter_list->hasFilter('value')) {
	// @todo: getdisplayvalue?
	$caption = "Reports with <code>".$filter_list->getFilter('platforminfo')."</code> = ".$filter_list->getFilter('value');
	$extension = null;
	if ($filter_list->belongsToExtension($filter_list->getFilter('platforminfo'), $filter_list::PlatformInfo, $extension)) {
		$subcaption = "Part of the <code>$extension</code> extension";
	}
}
$defaultHeader = !($filter_list->hasFilters());

// Platform (os)
$platform = 'all';
if ($filter_list->hasFilter('platform')) {
	$platform = $filter_list->getFilter('platform');
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
	?>
	<!-- Compare block (only visible when at least one report is selected) -->
	<div id="compare-div" class="well well-sm" role="alert" style="text-align: center; display: none;">
		<div class="compare-header">Selected reports for compare:</div>
		<span id="compare-info"></span>
		<div class="compare-footer">
			<Button onClick="clearCompare()"><span class='glyphicon glyphicon-button glyphicon-erase'></span> Clear</Button>
			<Button onClick="compare()"><span class='glyphicon glyphicon-button glyphicon-duplicate'></span> Compare</Button>
		</div>
	</div>	
	<?php
	PageGenerator::platformNavigation('listreports.php', $platform, true, $filter_list->filters);
	?>
	<div class='tablediv tab-content' style='display: inline-flex;'>
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
					<th>API version</th>
					<th>Type</th>
					<th>OS</th>
					<th>Version</th>
					<th>Platform</th>
					<th>Compare</th>
				</tr>
			</thead>
		</table>
		<div id="errordiv" style="color:#D8000C;"></div>
	</div>
</center>

<script src="js/reportcompare.js"></script>

<script>
	$(document).on("keypress", "form", function(event) {
		return event.keyCode != 13;
	});

	$(document).ready(function() {

		$.get(comparerUrl, null, function (response) {
			displayCompare(response);
		});	

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
					foreach ($filter_list->filters as $filter => $value) {
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