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

// Header
$defaultHeader = true;
$pageTitle = null;
$caption = null;
$negate = false;
$showTabs = true;
if (isset($_GET['option'])) {
	if ($_GET['option'] == 'not') {
		$negate = true;
	}
}
$platform = "all";
if (isset($_GET['platform'])) {
	$platform = GET_sanitized('platform');
}

// Extension
$extension = GET_sanitized('extension');
if ($extension != '') {
	$defaultHeader = false;
	$caption = "Reports " . ($negate ? "<b>not</b>" : "") . " supporting <code>" . $extension . "</code>";
}
// Submitter
$submitter = GET_sanitized('submitter');
if ($submitter != '') {
	$defaultHeader = false;
	$caption = "Reports submitted by <code>" . $submitter . "</code>";
}
// Device name
$devicename = GET_sanitized('devicename');
if ($devicename != '') {
	$defaultHeader = false;
	$caption = "Reports for <code>" . $devicename . "</code>";
}
// Platform (cl)
$platformname = GET_sanitized('platformname');
if ($platformname != '') {
	$defaultHeader = false;
	$caption = "Reports for platform <code>" . $platformname . "</code>";
}
// Platform (os)
if ($platform && $platform !== 'all') {
	if (!$caption) {
		$caption = "Listing reports";
	}
	$caption .= " on <img src='images/".$platform."logo.png' class='platform-icon'/>".ucfirst($platform);
	$defaultHeader = false;
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
		echo "<div class='header'><h4>";
		echo $caption ? $caption : "Listing available devices";
		echo "</h4></div>";
	}

	if ($showTabs) {
		PageGenerator::platformNavigation('listreports.php', $platform, true);
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
						'extension': 	'<?= GET_sanitized('extension') ?>',
						'submitter': 	'<?= GET_sanitized('submitter') ?>',
						'devicename': 	'<?= GET_sanitized('devicename') ?>',
						'displayname': 	'<?= GET_sanitized('displayname') ?>',
						'platformname': '<?= GET_sanitized('platformname') ?>',
						'platform': 	'<?= GET_sanitized('platform') ?>',
						'option': 		'<?= GET_sanitized('option') ?>'
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