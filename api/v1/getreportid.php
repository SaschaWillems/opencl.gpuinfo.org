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

$MAX_FILESIZE = 512 * 1024;
$upload_file_name = $_FILES['data']['name'];
if ($_FILES['data']['size'] > $MAX_FILESIZE) {
	echo "File exceeds size limitation of 512 KByte!";
	exit();
}

$ext = pathinfo($upload_file_name, PATHINFO_EXTENSION);
if ($ext != 'json') {
	echo "Report '$file' is not of file type json!";
	exit();
}

$path = './';
$file_name = uniqid('getreportid_', true) . 'json';
move_uploaded_file($_FILES['data']['tmp_name'], $path . $file_name) or exit('Error: Could not store report!');

$jsonFile = file_get_contents($file_name);
$report = json_decode($jsonFile, true);

try {
	header('HTTP/1.1 200 report_new');
	echo "-1";	
	/*
	DB::connect();
	$stmnt = DB::$connection->prepare("SELECT * from reports where
			devicename = :devicename and 
			driverversion = :driverversion and
			osname = :osname and
			osversion = :osversion and
			osarchitecture = :osarchitecture and
			apiversion = :apiversion and
			id = :reportid");
	$params = [
		'devicename' => $report['properties']['deviceName'],
		'driverversion' => $report['properties']['driverVersionText'],
		'osname' => $report['environment']['name'],
		'osversion' => $report['environment']['version'],
		'osarchitecture' => $report['environment']['architecture'],
		'apiversion' => $report['properties']['apiVersionText'],
		'reportid' => $reportid
	];
	$stmnt->execute($params);
	$report_match = $stmnt->rowCount() > 0;
	DB::disconnect();
	if (!$report_match) {
		header('HTTP/1.1 400 Devices do not match');
		exit();
	}
	*/
} finally {
	unlink($path . $file_name);
}

?>