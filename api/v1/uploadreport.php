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

function reportError($message, $file_name, $code = 500) {
	header("HTTP/1.1 $code Error while trying to upload report: $message");
	if (file_exists($file_name)) {
		unlink($file_name);
	}
	exit();
}

// @todo: check error handling (client and server)

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
	':openclversionmajor' => $report->getOpenCLValue('versionmajor'),
	':openclversionminor' => $report->getOpenCLValue('versionminor'),
	':reportversion' => $report->getEnvironmentValue('reportversion'),
	':appversion' => $report->getEnvironmentValue('appversion'),
	':submitter' => '', // @todo
	':description' => '' // @todo
];

// Check if all values required to uniquely identify the report are set
foreach ($params as $param) {
	if ($param === null) {
		reportError("Could not get required report identifiers", $file_name);
	}
}

DB::connect();
DB::$connection->beginTransaction();

// Report meta data	
try {	
	$sql = 
		"INSERT INTO reports
			(devicename, deviceversion, driverversion, openclversionmajor, openclversionminor, osname, osversion, osarchitecture, reportversion, appversion, description, submitter)
		VALUES
			(:devicename, :deviceversion, :driverversion, :openclversionmajor, :openclversionminor, :osname, :osversion, :osarchitecture, :reportversion, :appversion, :description, :submitter)";
	$stmnt = DB::$connection->prepare($sql);
	$stmnt->execute($params);
} catch (Exception $e) {
	reportError("Error at saving report meta data", $file_name);
}

// Get the id of the newly inserted report
$reportid = DB::$connection->lastInsertId();

// Store minified JSON for reference
try {	
	$sql = 
		"INSERT INTO reportsjson
			(reportid, json)
		VALUES
			(:reportid, :json)";
	$values = [
		':reportid' => $reportid,
		':json' => json_encode(json_decode($report->json))
	];
	$stmnt = DB::$connection->prepare($sql);
	$stmnt->execute($values);
} catch (Exception $e) {
	reportError("Error at saving report json data", $file_name);
}

// Report device info
try {	
	foreach ($report->deviceInfo() as $deviceInfo) {
		$sql = 
			"INSERT INTO deviceinfo 
				(reportid, name, enumvalue, extension, value)
			VALUES
				(:reportid, :name, :enumvalue, :extension, :value)";
		$values = [
			':reportid' => $reportid,
			':name' => $deviceInfo['name'],
			':enumvalue' => $deviceInfo['enumvalue'],
			':extension' => $deviceInfo['extension'],
			':value' => null
		];
		if (array_key_exists('value', $deviceInfo)) {
			if (is_array($deviceInfo['value'])) {
				$values[':value'] = serialize($deviceInfo['value']);
			} else {
				$values[':value'] = $deviceInfo['value'];
			}
		}

		$stmnt = DB::$connection->prepare($sql);
		$stmnt->execute($values);


		// @todo: details
	}
} catch (Exception $e) {
	reportError("Error at saving report device info", $file_name);
}

// Report device extensions
try {	
	foreach ($report->deviceExtensions() as $deviceExtension) {
		$sql = 
			"INSERT INTO deviceextensions 
				(reportid, name, version)
			VALUES
				(:reportid, :name, :version)";
		$values = [
			':reportid' => $reportid,
			':name' => $deviceExtension['name'],
			':version' => $deviceExtension['version'],
		];
		$stmnt = DB::$connection->prepare($sql);
		$stmnt->execute($values);
	}
} catch (Exception $e) {
	reportError("Error at saving report device extensions", $file_name);
}

// Report platform info
try {	
	foreach ($report->platformInfo() as $platformInfo) {
		$sql = 
			"INSERT INTO deviceplatforminfo 
				(reportid, name, enumvalue, extension, value)
			VALUES
				(:reportid, :name, :enumvalue, :extension, :value)";
		$values = [
			':reportid' => $reportid,
			':name' => $platformInfo['name'],
			':enumvalue' => $platformInfo['enumvalue'],
			':extension' => $platformInfo['extension'],
			':value' => null
		];
		if (array_key_exists('value', $platformInfo)) {
			if (is_array($platformInfo['value'])) {
				$values[':value'] = serialize($platformInfo['value']);
			} else {
				$values[':value'] = $platformInfo['value'];
			}
		}
		$stmnt = DB::$connection->prepare($sql);
		$stmnt->execute($values);
	
	}
} catch (Exception $e) {
	reportError("Error at saving report platform info", $file_name);
}

// Report platform extensions
try {	
	foreach ($report->platformExtensions() as $platformExtension) {
		$sql = 
			"INSERT INTO deviceplatformextensions 
				(reportid, name, version)
			VALUES
				(:reportid, :name, :version)";
		$values = [
			':reportid' => $reportid,
			':name' => $platformExtension['name'],
			':version' => $platformExtension['version'],
		];
		$stmnt = DB::$connection->prepare($sql);
		$stmnt->execute($values);
	}
} catch (Exception $e) {
	reportError("Error at saving report platform extensions", $file_name);
}

DB::$connection->commit();
DB::disconnect();

echo "res_uploaded";

unlink($file_name);	
?>