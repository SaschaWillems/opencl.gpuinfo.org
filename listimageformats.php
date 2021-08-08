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

	<div class='tablediv' style='width:auto; display: inline-block;'>
		<table id="formats" class="table table-striped table-bordered table-hover responsive" style='width:auto;'>
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
					if ($platform) {
						$params = ['ostype' => ostype($platform)];
						$where = "where r.ostype = :ostype";
					}
					$devicecount = DB::getCount("SELECT count(distinct devicename) from reports r $where", $params);
					$sql = 
						"SELECT 
						type,
						channelorder,
						channeltype, 
						sum(CL_MEM_READ_WRITE) as RW, 
						sum(CL_MEM_WRITE_ONLY) as WO, 
						sum(CL_MEM_READ_ONLY) as RO, 
						sum(CL_MEM_KERNEL_READ_AND_WRITE) as KRW,  
						count(distinct(r.devicename)) from deviceimageformats df 
						join reports r on r.id = df.reportid
						$where
						group by type,channelorder,channeltype";
					$stmnt = DB::$connection->prepare($sql);
					$stmnt->execute($params);
					$result = $stmnt->fetchAll(PDO::FETCH_ASSOC);

					foreach ($result as $format) {
						echo "<tr>";
						echo "<td>".$display_utils->displayMemObjectType($format['type'])."</td>";
						echo "<td>".$display_utils->displayChannelOrder($format['channelorder'])."</td>";
						echo "<td>".$display_utils->displayChannelType($format['channeltype'])."</td>";
						$cl_mem_flags = ['RW', 'WO', 'RO' , 'KRW'];
						foreach ($cl_mem_flags as $flag) {
							$coverage = ($format[$flag] / $devicecount) * 100.0;
							$class = ($coverage > 0) ? 'format-coverage-supported' : 'format-coverage-unsupported';
							if ($coverage > 75.0) {
								$class .= ' format-coverage-high';
							} elseif ($coverage > 50.0) {
								$class .= ' format-coverage-medium';
							} elseif ($coverage > 0.0) {
								$class .= ' format-coverage-low';
							}
							$link = null;
							echo "<td class='format-coverage'><a href='$link' class='$class'>" . round($coverage, 1) . "<span style='font-size:10px;'>%</span></a></td>";

						}
						echo "</tr>";
					}
				} catch (PDOException $e) {
					echo "<b>Error while fetcthing data!</b><br>";
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
				"fixedHeader": {
					"header": true,
					"headerOffset": 50
				},
				"order": [
					[0, "asc"]
				],
				"columnDefs": [{
					"targets": [1, 2],
				}]
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