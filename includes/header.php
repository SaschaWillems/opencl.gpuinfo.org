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
?>
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html" charset="ISO-8859-1">
	<?php echo "<title>" . (isset($page_title) ? ($page_title . " - OpenCL Hardware Database by Sascha Willems") : "OpenCL Hardware Database by Sascha Willems") . "</title>"; ?>

	<link rel="icon" type="image/png" href="/images/favicon_32px.png" sizes="32x32">

	<link rel="stylesheet" type="text/css" href="external/css/bootstrap.min.css" />
	<link rel="stylesheet" type="text/css" href="external/css/dataTables.bootstrap.min.css" />
	<link rel="stylesheet" type="text/css" href="external/css/responsive.bootstrap.min.css" />
	<link rel="stylesheet" type="text/css" href="external/bootstrap-toggle.min.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="external/css/fixedHeader.bootstrap.min.css" rel="stylesheet" />

	<link rel="stylesheet" type="text/css" href="style.css">

	<script type="text/javascript" src="external/jquery-2.2.0.min.js"></script>
	<script type="text/javascript" src="external/bootstrap.min.js"></script>
	<script type="text/javascript" src="external/jquery.dataTables.min.js"></script>
	<script type="text/javascript" src="external/jquery.dataTables.yadcf.js"></script>
	<script type="text/javascript" src="external/dataTables.bootstrap.min.js"></script>
	<script type="text/javascript" src="external/bootstrap-toggle.min.js"></script>
	<script type="text/javascript" src="external/dataTables.fixedHeader.min.js"></script>

	<!--	<script type="text/javascript" src="external/dataTables.responsive.min.js"></script> -->
	<script type="text/javascript" src="external/responsive.bootstrap.min.js"></script>

	<script>
		$(document).ready(function() {
			$.each($('#navbar').find('li'), function() {
				$(this).toggleClass('active',
					'/' + $(this).find('a').attr('href') == window.location.pathname);
			});
		});
		$(window).resize(function() {
			$('body').css('padding-top', parseInt($('#main-navbar').css("height")));
		});
		$(window).load(function() {
			$('body').css('padding-top', parseInt($('#main-navbar').css("height")));
		});
	</script>

	<meta name="twitter:card" content="summary" />
	<meta name="twitter:site" content="gpuinfo.org" />
	<meta name="twitter:creator" content="Sascha Willems" />

	<meta name="twitter:card" content="summary" />
	<meta name="twitter:site" content="@SaschaWillems2" />
	<meta name="twitter:title" content="OpenCL on gpuinfo.org" />
	<meta name="twitter:description" content="OpenCL hardware capability database." />
	<meta name="twitter:image" content="https://opencl.gpuinfo.org/images/opencl48.png" />
</head>

<body>
	<!-- Bootstrap nav bar -->
	<nav class="navbar navbar-default navbar-fixed-top" id="main-navbar">
		<div class="container-fluid">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a href="./listdevices.php">
					<img src="./images/opencl48.png" class="opencllogo">
				</a>
			</div>
			<div class="collapse navbar-collapse" id="myNavbar">
				<ul class="nav navbar-nav">
					<li><a href="listdevices.php">Devices</a></li>
					<li><a href="listreports.php">Reports</a></li>
					<li><a href="listdeviceinfo.php">Deviceinfo</a></li>
					<li><a href="listextensions.php">Extensions</a></li>
					<li><a href="listimageformats.php">Formats</a></li>
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">Platforms
							<span class="caret"></span></a>
						<ul class="dropdown-menu">
							<li><a href="listplatforms.php">List</a></li>
							<li><a href="listplatforminfo.php">Info</a></li>
							<li><a href="listplatformextensions.php">Extensions</a></li>
						</ul>
					</li>
					<li><a href="download.php">Download</a></li>
					<li><a href="about.php">About</a></li>
				</ul>
				<ul class="nav navbar-nav navbar-right">
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">gpuinfo.org
							<span class="caret"></span></a>
						<ul class="dropdown-menu">
							<li><a href="https://opengl.gpuinfo.org">OpenGL</a></li>
							<li><a href="https://opengles.gpuinfo.org">OpenGL ES</a></li>
							<li><a href="https://opencl.gpuinfo.org">OpenCL</a></li>
							<li><a href="https://vulkan.gpuinfo.org">Vulkan</a></li>
							<li role="separator" class="divider"></li>
							<li><a href="https://android.gpuinfo.org">Android</a></li>
							<li role="separator" class="divider"></li>
							<li><a href="https://www.gpuinfo.org">Launchpad</a></li>
						</ul>
					</li>
				</ul>
			</div>
		</div>
	</nav>