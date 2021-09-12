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

PageGenerator::header("Platform info");
?> 

<div class='header'>
	<?php echo "<h4>Platform info coverage for ".PageGenerator::platformInfo($platform) ?>
</div>

<center>	
<?php PageGenerator::platformNavigation('listplatforminfo.php', $platform, true); ?>
<div class='tablediv' style='width:auto; display: inline-block;'>
	<table id="deviceinfo" class="table table-striped table-bordered table-hover" >
		<thead>
			<tr>				
				<th>Name</th>
				<th>Coverage</th>
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
							name, 
							count(distinct devicename) as coverage
						from deviceplatforminfo d
						join reports r on r.id = d.reportid
						$where
						group by name";
					$stmnt = DB::$connection->prepare($sql);
					$stmnt->execute($params);
					while ($row = $stmnt->fetch(PDO::FETCH_ASSOC)) {
						$link = "displayplatforminfo.php?name=".$row['name'];
						if ($platform) {
							$link .= "&platform=$platform";
						};						
						echo "<tr>";						
						echo "<td><a href='$link'>".$row['name']."</a></td>";
						echo "<td align=center>".round($row['coverage'] / $devicecount * 100, 1)."%</td>";
						echo "</tr>";	    
					}    
				} catch (PDOException $e) {
					echo "<b>Error while fetching platform info list</b><br>";
				}							        			
				DB::disconnect();
			?>   															
		</tbody>
	</table> 
</div>

<script>
	$(document).ready(function() {
		var table = $('#deviceinfo').DataTable({
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
			]
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