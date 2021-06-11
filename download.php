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
PageGenerator::header('Download');
?>

<div class="panel panel-default">
	<div class="panel-body text-content-block">
		<div class="page-header">
			<h2>Downloads</h2>
		</div>
		<div>
			The database is populated using the OpenCL Hardware Capability Viewer application, available for multiple platforms. It reads and displays Vulkan related information for a selected implementation, and that data can then be uploaded to the database.
			The OpenCL Hardware Capability Viewer is a free open source product and the sources are always available from <a href="https://github.com/SaschaWillems/OpenCLCapsViewer">this github repository</a>.<br>
		</div>
		<div class="page-header">
			<h3>Current beta release 1.00</h3>
			<ul>		
				<li>Windows
					<ul>
						<li><a href="downloads/openclcapsviewer_1.00_beta_win64.zip">Windows 64-bit (zip)</a></li>
						<!-- <li><a href="downloads/vulkancapsviewer_3.01_x86.zip">Windows 32-bit (zip)</a><br/><b>Please note:</b> The 32-bit windows release should only be run on platforms that don't support 64-bit!<br/>Some Vulkan implementations may not expose all hardware capabilities when run under 32 bits.</li> -->
					</ul>
				</li>
				<li>Linux
					<ul>
						<li><a href="downloads/openclcapsviewer_1.00_beta_linux64.AppImage">X11 x86-64</a> (AppImage)</li>
						<!-- <li><a href="downloads/vulkancapsviewer_3.01_linux64_wayland.AppImage">Wayland x86-64</a> (AppImage)</li> -->
					</ul>
				</li>
				<li>Android
					<ul>
						<li><a href="downloads/openclcapsviewer_1.00_beta_arm.apk">Android arm-v8 (apk)</a></li>
					</ul>
			</ul>
		</div>
		<div class="page-header">
			<h3>Release notes</h3>
			<h4>1.00 - 2021-xx-yy</h4>
			<ul>
				<li>First release</li>
			</ul>
		</div>
	</div>
</div>

<?php PageGenerator::footer(); ?>

</body>
</html>