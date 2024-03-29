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

require 'pagegenerator.php';
require './database/database.class.php';
require './includes/functions.php';
require './includes/displayutils.php';
require './reportcompare/reportcompare.class.php';

PageGenerator::header('Compare reports');

DB::connect();

// Use url parameter to enable diff only display
$diff = false;
if (isset($_GET['diff'])) {
	$diff = (int)($_GET['diff']) == 1;
}

$headerFields = array("device", "driverversion", "apiversion", "os");

$reportids = array();
$reportlimit = false;

if ((!isset($_REQUEST['id'])) && (!isset($_REQUEST['devices'])) && (!isset($_REQUEST['reports']))) {
	PageGenerator::errorMessage("<strong>No report IDs set!</strong>");
}

// The number of reports that can be compared is limited due to performance and layout
$maxReportCount = 4;

$reportids = [];

// Compare from report list (old format)
if (isset($_REQUEST['id'])) {
	foreach ($_REQUEST['id'] as $key => $value) {
		$reportids[] = (int)($key);
	}
}

// Compare from report list (new format)
// The URL contains a comma separated list of report ids dot compare
// e.g. compare.php/reports=100,200,900
if (isset($_REQUEST['reports'])) {
	$params = explode(',', $_REQUEST['reports']);
	foreach($params as $param) {
		if (is_numeric($param)) {
			$reportids[] = intval($param);
		}
	}
	$_SESSION['opencl_compare_reports'] = [];
}

// Compare from device list
if (isset($_REQUEST['devices'])) {
	foreach ($_REQUEST['devices'] as $device) {
		$device_id = explode('&os=', $device);
		$reportids[] = ReportCompare::getLatestReport($device_id[0], $device_id[1]);
	}
	$_SESSION['opencl_compare_devices'] = [];
}

if (empty($reportids)) {
	PageGenerator::errorMessage("<strong>No report IDs set!</strong>");
}

// Limit max. number of reports to compare
$reportlimit = false;
if (count($reportids) > $maxReportCount) {
	$reportlimit = true;
	$reportids = array_slice($reportids, 0, $maxReportCount);
}


$display_utils = new DisplayUtils();
$report_compare = new ReportCompare($reportids);
$report_compare->fetchData();

?>
<div class='header'>
	<h4 style='margin-left:10px;'>Comparing <?= $report_compare->report_count ?> reports</h4>
	<label id="toggle-label" class="checkbox-inline" style="display:none;">
		<input id="toggle-event" type="checkbox" data-toggle="toggle" data-size="small" data-onstyle="success"> Display only different values
	</label>
</div>

<?php
if ($reportlimit) {
	echo "<b>Note : </b>You selected more than 4 reports to compare, only displaying the first 4 selected reports.";
}

echo "<center><div id='reportdiv'>";

$colspan = count($reportids) + 1;
?>

<div>
	<ul class='nav nav-tabs nav-report'>
		<li class='active'><a data-toggle='tab' href='#deviceinfo'>Device info</a></li>
		<li><a data-toggle='tab' href='#deviceextensions'>Extensions</a></li>
		<li><a data-toggle='tab' href='#deviceimageformats'>Image formats</a></li>
		<li><a data-toggle='tab' href='#deviceplatform'>Platform</a></li>
	</ul>
</div>

<div class='tablediv tab-content report-display'>

	<div id="overlay_devices">
		<center>
			<h4>Fetching data...</h4><img src="./images/loading.gif">
		</center>
	</div>

	<?php
	$views = [
		'deviceinfo',
		'deviceextensions',
		'deviceimageformats',
		'deviceplatform',
	];
	foreach ($views as $index => $view) {
		echo "<div id='$view' class='tab-pane fade ".($index == 0 ? "in active" : null)." reportdiv'>";
			include "reportcompare/$view.php";
		echo "</div>";
	}		
	DB::disconnect();
	?>

	<script>
		$(document).ready(function() {
			var tableNames = [
				'device-info-table',
				'device-extensions-table',
				'platform-info-table',
				'platform-extensions-table'
			];
			for (var i = 0, arrlen = tableNames.length; i < arrlen; i++) {
				if (typeof $('#'+tableNames[i]) != undefined) {
					$('#' + tableNames[i]).dataTable({
						"pageLength": -1,
						"paging": false,
						"order": [],
						"searchHighlight": true,
						"sDom": 'flpt',
						"deferRender": true,
						"fixedHeader": {
							"header": true,
							"headerOffset": 50
						},
					});
				}
			}

			// Grouped tables
			tableNames = [
				'device-image-formats',
			];

			// Device properties table with grouping
			for (var i = 0, arrlen = tableNames.length; i < arrlen; i++) {
				if (typeof $('#'+tableNames[i]) != undefined) {
					$('#' + tableNames[i]).dataTable({
						"pageLength": -1,
						"paging": false,
						"order": [],
						"columnDefs": [
							{ visible: false, targets: 0 },
							{ width: 40, targets: [3, 4, 5, 6] },
							{ orderable: false, targets: [3, 4, 5, 6] }
						],
						"searchHighlight": true,
						"bAutoWidth": false,
						"sDom": 'flpt',
						"deferRender": true,
						"processing": true,
						"fixedHeader": {
							"header": true,
							"headerOffset": 50
						},
						"drawCallback": function(settings) {
							var api = this.api();
							var rows = api.rows({
								page: 'current'
							}).nodes();
							var last = null;
							api.column(0, {
								page: 'current'
							}).data().each(function(group, i) {
								if (last !== group) {
									$(rows).eq(i).before(
										'<tr><td colspan="' + api.columns().header().length + '" class="group">' + group + '</td></tr>'
									);
									last = group;
								}
							});
						}
					});
				}
			}

			$('#devices').show();
			$("#overlay_devices").hide();
			$("#toggle-label").show();
		});

		$('#toggle-event').change(function() {
			if ($(this).prop('checked')) {
				$('.same').hide();
			} else {
				$('.same').show();
			}
		});

		$('a[data-toggle="tab"]').on("shown.bs.tab", function(e) {
			$($.fn.dataTable.tables()).DataTable().fixedHeader.adjust();
		});

		// Activate tab selected via anchor
		$(function() {
			var a = document.location.hash;
			if (a) 
			{
				// Nested tabs, need to show parent tab too
				if ((a === '#platform-info') || (a === '#platform-extensions')) {
					$('.nav a[href=\\#deviceplatform]').tab('show');
				}
				$('.nav a[href=\\'+a+']').tab('show');
			}			
			$('a[data-toggle="tab"]').on('show.bs.tab', function(e) {
				window.location.hash = e.target.hash;
			});
		});
	</script>

</div>

<?php PageGenerator::footer(); ?>

</body>

</html>