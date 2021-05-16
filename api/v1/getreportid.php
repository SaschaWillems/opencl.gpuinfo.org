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

include './../../database/database.class.php';	
include './report.class.php';

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

try {
	$source = file_get_contents($file_name);
	$report = new Report();
	$report->fromJson($source);
	
	$params = [
		':devicename' => $report->getDeviceInfoValue('CL_DEVICE_NAME'),
		':deviceversion' => $report->getDeviceInfoValue('CL_DEVICE_VERSION'),
		':driverversion' => $report->getDeviceInfoValue('CL_DRIVER_VERSION'),
		':osname' => $report->getEnvironmentValue('name'),
		':osversion' => $report->getEnvironmentValue('version'),
		':osarchitecture' => $report->getEnvironmentValue('architecture'),
	];
	
	// Check if all values required to uniquely identify the report are set
	foreach ($params as $param) {
		if ($param === null) {
			header('HTTP/1.1 500 Could not get required report identifiers');
			if (file_exists($file_name)) {
				unlink($file_name);
			}
			exit();
		}
	}

	DB::connect();
	try {
		$sql = 
			"SELECT id from reports where 
				devicename = :devicename and 
				deviceversion = :deviceversion and
				driverversion = :driverversion and
				osname = :osname and
				osversion = :osversion and
				osarchitecture = :osarchitecture";
		try {
			$stmnt = DB::$connection->prepare($sql);
			$stmnt->execute($params);
			$row = $stmnt->fetch(PDO::FETCH_NUM);
			if ($stmnt->rowCount() > 0) {
				$reportid = $row[0];
				header('HTTP/1.1 200 report_present');
				echo $reportid;
			} else {
				header('HTTP/1.1 200 report_not_present');
				echo "-1";
			}	
		} catch (Exception $e) {
			header('HTTP/1.1 500 Error while trying to get report information');
			if (file_exists($file_name)) {
				unlink($file_name);
			}		
			exit();
		}				
	} catch(Exception $e) {
		header('HTTP/1.1 500 Error while trying to get report information');
		if (file_exists($file_name)) {
			unlink($file_name);
		}	
		exit();
	}	
	DB::disconnect();
} catch (Throwable $e) {
	header('HTTP/1.1 500 Error while trying to get report information');
} finally {
	if (file_exists($file_name)) {
		unlink($file_name);
	}
}

?>