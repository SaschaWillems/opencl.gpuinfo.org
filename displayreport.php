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

require 'pagegenerator.php';
require './database/database.class.php';
require './includes/functions.php';
require './reportdisplay/reportdisplay.class.php';

$reportID = $_GET['id'];
if (!$reportID) {
	PageGenerator::errorMessage("<strong>Warning!</strong><br> No report ID set to display!");
}

$report = new Report($reportID);
$report->fetchData();

if (!$report->exists()) {
	PageGenerator::errorMessage("
		<strong>This is not the <strike>droid</strike> report you are looking for!</strong><br><br>
		Could not find report with ID <?php echo $reportID; ?> in database.<br>
		It may have been removed due to faulty data."
	);
}

PageGenerator::header($report->info->device_description);

DB::connect();
echo "<center>";

// Header
$header = "Device report for " . $report->info->device_description;
if ($report->info->platform !== null) {
	$header .= " on <img src='images/" . $report->info->platform . "logo.png' height='14px' style='padding-right:5px'/>" . ucfirst($report->info->platform);
}
echo "<div class='header'>";
echo "<h4>$header</h4>";
echo "</div>";

?>
<div>
	<ul class='nav nav-tabs nav-report'>
		<li class='active'><a data-toggle='tab' href='#deviceinfo'>Device info</a></li>
		<li><a data-toggle='tab' href='#deviceextensions'>Extensions</a></li>
		<li><a data-toggle='tab' href='#deviceplatform'>Platform</a></li>
	</ul>
</div>

<div class='tablediv tab-content' style='width:75%;'>

	<?php
	$views = [
		'deviceinfo',
		'deviceextensions',
		'deviceplatform',
	];
	foreach ($views as $index => $view) {
		echo "<div id='$view' class='tab-pane fade ".($index == 0 ? "in active" : null)." reportdiv'>";
			include "reportdisplay/$view.php";
		echo "</div>";
	}	
	// if ($report->flags->has_update_history) {
	// 	include 'reportdisplay/history.php';
	// }
	?>

	<script>
		$(document).ready(
			function() {
				var tableNames = [
					'table_deviceinfo',
					'table_deviceextensions',
					'table_deviceplatforminfo',
					'table_deviceplatformextensions'
				];
				for (var i = 0, arrlen = tableNames.length; i < arrlen; i++) {
					if (typeof $('#' + tableNames[i]) != undefined) {
						$('#' + tableNames[i]).dataTable({
							"pageLength": -1,
							"paging": false,
							"order": [],
							"searchHighlight": true,
							"bAutoWidth": false,
							"sDom": 'flpt',
							"deferRender": true,
							"processing": true
						});
					}
				}
			});

		$(function() {
			var a = document.location.hash;
			if (a) {
				// Nested tabs, need to show parent tab too
				if ((a === '#platform_info') || (a === '#platform_extensions')) {
					$('.nav a[href=\\#platform]').tab('show');
				}
				$('.nav a[href=\\' + a + ']').tab('show');
			}

			$('a[data-toggle="tab"]').on('show.bs.tab', function(e) {
				window.location.hash = e.target.hash;
			});
		});
	</script>
</div>

<?php PageGenerator::footer(); ?>

</center>

</body>

</html>