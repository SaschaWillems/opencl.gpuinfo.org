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
PageGenerator::header('About');
?>

<div id='reportdiv'>	   
	<div class="panel panel-default">
		<div class="panel-body text-content-block">
			<div class="page-header">
				<h2>About the OpenCL Hardware Database</h2>
			</div>
			<div>
				Welcome to the community-driven OpenCL hardware database, an online tool for developers that want to check out device capabilites for the <a href="https://www.khronos.org/opencl">open standard</a> for parallel programming of heterogeneous systems.<br><br>
				This database and the client applications to submit reports are developed and maintained by me (<a href="https://www.saschawillems.de/" target="_blank">Sascha Willems</a>) in my spare time.<br><br>
				No profit is made from this data, nor is this data used in any commercial way and no personal data is transferred, stored or passed.<br><br>
				If you want to contribute to the development, you can find the source code for the client application <a href="https://github.com/SaschaWillems/OpenCLCapsViewer" target="_blank">here</a> and the source code for the database <a href="https://github.com/SaschaWillems/opencl.gpuinfo.org" target="_blank">here</a>.<br><br>
				Thanks to the authors of <a href="https://www.datatables.net/" target="_blank">datatables</a>, <a href="https://github.com/vedmack/yadcf" target="_blank">yadcf</a> and <a href="https://github.com/apexcharts/apexcharts.js">ApexCharts</a> which are used by the front-end of the database.<br><br>
			</div>			
			<!-- <div class="page-header">
				<h2>Donating</h2>
			</div>
			<div>
				All of my tools and the database itself are free-to-use, open source and hosted by me free of charge, feel free to donate ;)
			</div>
			<div>
				<h3>PayPal</h3>
				<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=BHXPMV6ZKPH9E"><img alt="" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif"/></a>
			</div>				 -->
		</div>    
	</div>
</div>

<?php PageGenerator::footer(); ?>

</body>
</html>

