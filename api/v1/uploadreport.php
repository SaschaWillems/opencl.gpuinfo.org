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

// include "./../../includes/functions.php";
include './../../database/database.class.php';	
include './report.class.php';

// Check for valid file

// Check filesize
$MAX_FILESIZE = 512 * 1024;
$file = $_FILES['data']['name'];
if ($_FILES['data']['size'] > $MAX_FILESIZE)  {
	echo "File exceeds size limitation of 512 KByte!";
	exit();  
}
if ($_FILES['data']['size'] == 0) {
	echo "Provided report file is empty, check client write permissions!";
	exit();  
}	

// Check file extension 
$ext = pathinfo($_FILES['data']['name'], PATHINFO_EXTENSION); 
if ($ext != 'json') {
	echo "Report '$file' is not of file type json!";
	exit();  
} 

$path = './';
$file_name = uniqid('uploadreport_', true) . '.json';
move_uploaded_file($_FILES['data']['tmp_name'], $path . $file_name) or exit('Error: Could not store report!');

function convertValue($val) {
	if (is_string($val)) {
		if (strpos($val, '0x') === 0) {
			return hexdec($val);
		}
	} else {
		return $val;
	}

}

// @todo: try except

$source = file_get_contents($file_name);
$report = new Report();
$report->fromJson($source);

$params = [
	':devicename' => $report->getDeviceInfoValue('DEVICE_NAME'),
	':deviceversion' => $report->getDeviceInfoValue('DEVICE_VERSION'),
	':driverversion' => $report->getDeviceInfoValue('DRIVER_VERSION'),
	':osname' => $report->getEnvironmentValue('name'),
	':osversion' => $report->getEnvironmentValue('version'),
	':osarchitecture' => $report->getEnvironmentValue('architecture'),
	':openclversionmajor' => $report->getOpenCLValue('versionmajor'),
	':openclversionminor' => $report->getOpenCLValue('versionminor'),
	':reportversion' => $report->getEnvironmentValue('reportversion'),
	':submitter' => '', // @todo
	':description' => '' // @todo
];

// Check if all values required to uniquely identify the report are set
foreach ($params as $param) {
	if ($param === null) {
		header('HTTP/1.1 500 Could not get required report identifiers');
		unlink($file_name);
		exit();
	}
}

DB::connect();
DB::$connection->beginTransaction();

try {
	
	// Report meta data	
	$sql = 
		"INSERT INTO reports
			(devicename, deviceversion, driverversion, openclversionmajor, openclversionminor, osname, osversion, osarchitecture, reportversion, description, submitter)
		VALUES
			(:devicename, :deviceversion, :driverversion, :openclversionmajor, :openclversionminor, :osname, :osversion, :osarchitecture, :reportversion, :description, :submitter)";
	try {
		$stmnt = DB::$connection->prepare($sql);
		$stmnt->execute($params);
		$reportid = DB::$connection->lastInsertId();
	} catch (Exception $e) {
		header('HTTP/1.1 500 Error while trying to upload report (error at report meta data)');
		exit();
	}				
} catch(Exception $e) {
	header('HTTP/1.1 500 Error while trying to upload report (error at report meta data)');
	exit();
}

DB::$connection->commit();
DB::disconnect();

echo "res_uploaded";

unlink($file_name);	
?>