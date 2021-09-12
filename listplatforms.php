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

$platform = null;
if (isset($_GET['platform'])) {
	$platform = GET_sanitized('platform');
}

PageGenerator::header("Extensions");
?>

<div class='header'>
	<?php echo "<h4>Platform listing for ".PageGenerator::platformInfo($platform) ?>
</div>

<center>
	<?php PageGenerator::platformNavigation('listplatforms.php', $platform, true); ?>
	<div class='tablediv' style='width:auto; display: inline-block;'>
		<table id="platforms" class="table table-striped table-bordered table-hover responsive" style='width:auto;'>
			<thead>
				<tr>
					<th>Name</th>
					<th>Reports</th>
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
							value, 
							count(*) as reports 
						from deviceplatforminfo d 
						join reports r on r.id = d.reportid
						$where ".($where ? "and" : "where")." name = 'CL_PLATFORM_NAME' group by value";
					$stmnt = DB::$connection->prepare($sql);
					$stmnt->execute($params);
					$platforms = $stmnt->fetchAll(PDO::FETCH_ASSOC);
					foreach ($platforms as $platform) {
						$link = "listreports.php?platformname=".$platform['value'];
						echo "<tr>";
						echo "<td>".$platform['value']."</td>";
						echo "<td class='text-center'><a href=\"$link\">".$platform['reports']."</a></td>";
						echo "</tr>";
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
			var table = $('#platforms').DataTable({
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