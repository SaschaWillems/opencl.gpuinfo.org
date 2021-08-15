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

/*
 * Stores a new report in the database
 */

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
	':devicename' => $report->getDeviceIdentifier('devicename'),
	':gpuname' =>  $report->getDeviceIdentifier('gpuname'),
	':deviceversion' => $report->getDeviceIdentifier('deviceversion'),
	':driverversion' => $report->getDeviceIdentifier('driverversion'),	
	// @todo: combine all identifier values?
	':deviceidentifier' => $report->getDeviceIdentifier('devicename')." ".$report->getDeviceIdentifier('deviceversion'),
	':devicetype' => $report->getDeviceInfoValue('CL_DEVICE_TYPE'),
	':osname' => $report->getEnvironmentValue('name'),
	':osversion' => $report->getEnvironmentValue('version'),
	':osarchitecture' => $report->getEnvironmentValue('architecture'),
	':ostype' => $report->getEnvironmentValue('type'),
	':openclversionmajor' => $report->getOpenCLValue('versionmajor'),
	':openclversionminor' => $report->getOpenCLValue('versionminor'),
	':reportversion' => $report->getEnvironmentValue('reportversion'),
	':appversion' => $report->getEnvironmentValue('appversion'),
	':submitter' => $report->getEnvironmentValue('submitter'),
	':comment' => $report->getEnvironmentValue('comment')
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
			(devicename, gpuname, deviceversion, driverversion, deviceidentifier, devicetype, openclversionmajor, openclversionminor, osname, osversion, osarchitecture, ostype, reportversion, appversion, submitter, comment)
		VALUES
			(:devicename, :gpuname, :deviceversion, :driverversion, :deviceidentifier, :devicetype, :openclversionmajor, :openclversionminor, :osname, :osversion, :osarchitecture, :ostype, :reportversion, :appversion, :submitter, :comment)";
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
		$detailid = DB::$connection->lastInsertId();

		// Additional details for this device info property
		if ((array_key_exists('details', $deviceInfo) && (is_array($deviceInfo['details'])))) {
			foreach($deviceInfo['details'] as $detail) {
				$sqlDetail = 
					"INSERT INTO deviceinfodetails 
						(deviceinfoid, reportid, name, detail, value)
					VALUES 
						(:deviceinfoid, :reportid, :name, :detail, :value)";
				$valuesDetail = [
					':deviceinfoid' => $detailid,
					':reportid' => $reportid,
					':name' => $detail['name'],
					':detail' => $detail['detail'],
					':value' => $detail['value'],
				];
				$stmntDetail = DB::$connection->prepare($sqlDetail);
				$stmntDetail->execute($valuesDetail);				
			}
		}
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

// Report device image formats
try {	
	foreach ($report->deviceImageFormats() as $deviceImageFormat) {
		$sql = 
			"INSERT INTO deviceimageformats 
				(reportid, type, channelorder, channeltype, flags, CL_MEM_READ_WRITE, CL_MEM_WRITE_ONLY, CL_MEM_READ_ONLY, CL_MEM_KERNEL_READ_AND_WRITE)
			VALUES
				(:reportid, :type, :channelorder, :channeltype, :flags, :CL_MEM_READ_WRITE, :CL_MEM_WRITE_ONLY, :CL_MEM_READ_ONLY, :CL_MEM_KERNEL_READ_AND_WRITE)";
		$values = [
			':reportid' => $reportid,
			':type' => $deviceImageFormat['type'],
			':channelorder' => $deviceImageFormat['channelorder'],
			':channeltype' => $deviceImageFormat['channeltype'],
			':flags' => $deviceImageFormat['flags'],
		];
		// Explicitly store supported flags as columns
		// Makes it much easier to evaluate these in database statements
		$cl_mem_flags = [
			':CL_MEM_READ_WRITE' => (1 << 0),
			':CL_MEM_WRITE_ONLY' => (1 << 1),
			':CL_MEM_READ_ONLY' => (1 << 2),
			':CL_MEM_KERNEL_READ_AND_WRITE' => (1 << 12)
		];
		foreach ($cl_mem_flags as $key => $value) {
			$values[$key] = ($deviceImageFormat['flags'] & $value) ? 1 : 0;
		}
		$stmnt = DB::$connection->prepare($sql);
		$stmnt->execute($values);
	}
} catch (Exception $e) {
	reportError("Error at saving report device image formats", $file_name);
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

try {
	$msgtitle = "New OpenCL report for ".$params[':devicename']." ".$params[':deviceversion']. " ".$params[':driverversion'];
	$msg = "New OpenCL hardware report uploaded to the database\n\n";
	$msg .= "Link : https://opencl.gpuinfo.org/displayreport.php?id=$reportid\n\n";	
	foreach ($params as $key => $value) {
		$msg .= ucfirst(str_replace(":", "", $key))." = ".$value."\n";
	}	
	mail($mailto, $msgtitle, $msg);
} catch (Exception $e) {
	// Failure to mail is not critical
}	
?>