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
PageGenerator::header('Download');
?>

<div class="panel panel-default">
	<div class="panel-body text-content-block">
		<div class="page-header">
			<h2>Downloads</h2>
		</div>
		<div>
			The database is populated using the OpenCL Hardware Capability Viewer application, available for multiple platforms. It reads and displays OpenCL related information for a selected implementation, and that data can then be uploaded to the database.
			The OpenCL Hardware Capability Viewer is a free open source product and the sources are always available from <a href="https://github.com/SaschaWillems/OpenCLCapsViewer">this github repository</a>.<br>
		</div>
		<div class="page-header">
			<h3>Current release</h3>
			<ul>		
				<li>Windows
					<ul>
						<li><a href="downloads/openclcapsviewer_1.20_win64.zip">Windows 64-bit</a> (zip)</li>
					</ul>
				</li>
				<li>Linux
					<ul>
						<li><a href="downloads/openclcapsviewer_1.20_linux64_x11.AppImage">X11 x86-64</a> (AppImage)</li>
						<li><a href="downloads/openclcapsviewer_1.20_linux64_cli.AppImage">CLI x86-64</a> (AppImage)</li>
					</ul>
				</li>
				<li>Android
					<ul>
						<li><a href="downloads/openclcapsviewer_1.20_arm.apk">Android arm-v8</a> (apk)</li>
						<li><a href="https://play.google.com/store/apps/details?id=de.saschawillems.openclcapsviewer&hl=en_US&gl=US">Download from Google Play</a></li> 
					</ul>
			</ul>
		</div>
		<div class="page-header">
			<h3>Release notes</h3>
			<h4>1.20 - 2022-11-05</h4>
			<ul>
				<li>Fixes some version checks that were too strict, resulting in some missing queries for OpenCL 3.0 implementations</li>
			</ul>
			<h4>1.10 - 2022-07-02</h4>
			<ul>
				<li>Added a new command line only release for windows to be used on systems without user interfaces</li>
				<li>Adds command line arguments for uploading reports, see <a href="https://github.com/SaschaWillems/OpenCLCapsViewer/blob/master/docs/commandline_arguments.md"></a></li>
			</ul>
			<h4>1.00 - 2022-01-11</h4>
			<ul>
				<li>First release</li>
				<ul>
					<li><b>Note:</b> This is the first public release</li>
					<li>Support for OpenCL 1.x, 2.x and 3.x</li>
				</ul>
			</ul>
		</div>
	</div>
</div>

<?php PageGenerator::footer(); ?>

</body>
</html>