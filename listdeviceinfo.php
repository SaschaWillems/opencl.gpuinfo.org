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

$platform = "windows";
if (isset($_GET['platform'])) {
	$platform = $_GET['platform'];
}

PageGenerator::header("Device info");
?> 

<div class='header'>
	<?php echo "<h4>Device info coverage for ".PageGenerator::platformInfo($platform) ?>
</div>

<center>	
<?php PageGenerator::platformNavigation('listdeviceinfo.php', $platform); ?>

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
					$devicecount = DB::getCount("SELECT count(distinct deviceidentifier) from reports where ostype = :ostype", ['ostype' => ostype($platform)]);	
					$sql = 
						"SELECT 
							name, 
							count(distinct deviceidentifier) as coverage
						from deviceinfo d
						join reports r on r.id = d.reportid
						where r.ostype = :ostype
						group by name";
					$stmnt = DB::$connection->prepare($sql);
					$stmnt->execute(['ostype' => ostype($platform)]);
					while ($row = $stmnt->fetch(PDO::FETCH_ASSOC)) {
						$link = "displaydeviceinfo.php?name=".$row['name']."&platform=$platform";
						echo "<tr>";						
						echo "<td><a href='$link'>".$row['name']."</a></td>";
						echo "<td align=center>".round($row['coverage'] / $devicecount * 100, 1)."%</td>";
						echo "</tr>";	    
					}    
				} catch (PDOException $e) {
					echo "<b>Error while fetching device info list</b><br>";
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