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
require './includes/displayutils.php';

$platform = null;
if (isset($_GET['platform'])) {
	$platform = GET_sanitized('platform');
}

PageGenerator::header("Image formats");
$display_utils = new DisplayUtils();
?>

<div class='header'>
	<?php echo "<h4>Image format coverage for ".PageGenerator::platformInfo($platform) ?>
</div>

<center>
	<?php PageGenerator::platformNavigation('listimageformats.php', $platform, true); ?>

	<div class='tablediv' style='width:auto; display: inline-block; '>
		<div id="fetching">Fetching data...</div>
		<table id="formats" class="table table-striped table-bordered table-hover responsive" style='width:auto; display:none;'>
			<thead>
				<tr>
					<th>Format</th>
					<th>Channel order</th>
					<th>Channel type</th>
					<th><abbr title="CL_MEM_READ_WRITE">RW</abbr></th>
					<th><abbr title="CL_MEM_WRITE_ONLY">WO</abbr></th>
					<th><abbr title="CL_MEM_READ_ONLY">RO</abbr></th>
					<th><abbr title="CL_MEM_KERNEL_READ_AND_WRITE">KRW</abbr></th>
				</tr>				
			</thead>			
			<tbody>
				<?php
				DB::connect();
				try {
					$params = [];
					$where = null;
					$whereAnd = null;
					if ($platform) {
						$params = ['ostype' => ostype($platform)];
						$where = "where r.ostype = :ostype";
						$whereAnd = "and r.ostype = :ostype";
					}
					$devicecount = DB::getCount("SELECT count(distinct devicename) from reports r $where", $params);
					$sql = 
						"SELECT type, channelorder, channeltype,
						0 as CL_MEM_READ_WRITE, 
						0 as CL_MEM_WRITE_ONLY, 
						0 as CL_MEM_READ_ONLY, 
						0 as CL_MEM_KERNEL_READ_AND_WRITE
						from deviceimageformats df
						group by type,channelorder,channeltype
						order by type,channelorder,channeltype";
					$stmnt = DB::$connection->prepare($sql);
					$stmnt->execute($params);
					$result = $stmnt->fetchAll(PDO::FETCH_ASSOC);

					// Gather formats, channel orders and channel types into a nested structure
					$formats = [];
					foreach ($result as $fmt) {					
						$type = $fmt['type'];
						$c_order = $fmt['channelorder'];
						$c_type = $fmt['channeltype'];
						// Skip faulty entries
						if (($type == 0) || ($c_order == 0) || ($c_type == 0)) {
							continue;
						}
						if (!array_key_exists($type, $formats)) {
							$formats[$type] = [];
						}
						if (!array_key_exists($c_order, $formats[$type])) {
							$formats[$type][$c_order] = [];
						}
						if (!array_key_exists($c_type, $formats[$type][$c_order])) {
							$formats[$type][$c_order][$c_type] = [
								'CL_MEM_READ_WRITE' => 0, 
								'CL_MEM_WRITE_ONLY' => 0, 
								'CL_MEM_READ_ONLY' => 0,
								'CL_MEM_KERNEL_READ_AND_WRITE' => 0
							];
						}
					}

					$cl_mem_flags = ['CL_MEM_READ_WRITE', 'CL_MEM_WRITE_ONLY', 'CL_MEM_READ_ONLY' , 'CL_MEM_KERNEL_READ_AND_WRITE'];

					foreach ($cl_mem_flags as $flag) {
						$sql = 
							"SELECT type, channelorder, channeltype, count(distinct(r.devicename)) as devices from deviceimageformats df 
							join reports r on r.id = df.reportid
							where $flag = 1
							$whereAnd
							group by type,channelorder,channeltype
							order by type,channelorder,channeltype";
						$stmnt = DB::$connection->prepare($sql);
						$stmnt->execute($params);
						$result = $stmnt->fetchAll(PDO::FETCH_ASSOC);
						foreach ($result as $fmt_suppport) {
							$formats[$fmt_suppport['type']][$fmt_suppport['channelorder']][$fmt_suppport['channeltype']][$flag] += $fmt_suppport['devices'];						
						}
					}

					foreach ($formats as $f_type => $f_types) {
						foreach ($f_types as $c_order => $c_orders) {
							foreach ($c_orders as $c_type => $coverages) {
								echo "<tr>";
								echo "<td>".$display_utils->displayMemObjectType($f_type)."</td>";
								echo "<td>".$display_utils->displayChannelOrder($c_order)."</td>";
								echo "<td>".$display_utils->displayChannelType($c_type)."</td>";
								foreach ($cl_mem_flags as $flag) {
									$coverage = $coverages[$flag] / $devicecount * 100.0;
									$class = ($coverage > 0) ? 'format-coverage-supported' : 'format-coverage-unsupported';
									if ($coverage > 75.0) {
										$class .= ' format-coverage-high';
									} elseif ($coverage > 50.0) {
										$class .= ' format-coverage-medium';
									} elseif ($coverage > 0.0) {
										$class .= ' format-coverage-low';
									}
									$link = "listdevices.php?memobjecttype=$f_type&channelorder=$c_order&channeltype=$c_type&flag=".$flag;
									if ($platform) {
										$link .= "&platform=$platform";
									}
									echo "<td class='format-coverage'><a href='$link' class='$class'>" . round($coverage, 1) . "<span style='font-size:10px;'>%</span></a></td>";
								}
								echo "</tr>";
							}
						}
					}
				} catch (PDOException $e) {
					echo "<b>Error while fetching data!</b><br>";
				}
				DB::disconnect();
				?>
			</tbody>
		</table>
	</div>

	<script>
		$(document).ready(function() {
			var table = $('#formats').DataTable({
				"pageLength": -1,
				"paging": false,
				"stateSave": false,
				"searchHighlight": true,
				"dom": 'f',
				"bInfo": false,
				"bProcessing": true,
				"fixedHeader": {
					"header": true,
					"headerOffset": 50
				},
				"order": [
					[0, "asc"]
				],
				"columnDefs": [{
					"targets": [1, 2],
				}],
				"initComplete": function(){ 
					$("#formats").show(); 
					$("#fetching").hide(); 
				}
			});

			$("#searchbox").on("keyup search input paste cut", function() {
				table.search(this.value).draw();
			});			

		});
	</script>

	<?php PageGenerator::footer(); ?>

</center>
</body>

</html>