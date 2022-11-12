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

include '../../database/database.class.php';
include '../../includes/functions.php';
include '../../includes/displayutils.php';

DB::connect();

$data = [];
$params = [];
$searchClause = null;

$platform = null;

// Ordering
$orderByColumn = '';
$orderByDir = '';
if (isset($_REQUEST['order'])) {
    $orderByColumn = $_REQUEST['order'][0]['column'];
    $orderByDir = $_REQUEST['order'][0]['dir'];
    if (strcasecmp($orderByColumn, 'driver') == 0) {
        $orderByColumn = 'driverversionraw';
    }
    if (strcasecmp($orderByColumn, 'device') == 0) {
        $orderByColumn = 'devicename';
    }
}

// Paging
$paging = '';
if (isset($_REQUEST['start']) && $_REQUEST['length'] != '-1') {
    $paging = "LIMIT " . $_REQUEST["length"] . " OFFSET " . $_REQUEST["start"];
}

// Filtering
$searchColumns = [];
array_push($searchColumns, 'device', 'openclversion', 'driverversion');

// Per-column, filtering
$filters = array();
for ($i = 0; $i < count($_REQUEST['columns']); $i++) {
    $column = $_REQUEST['columns'][$i];
    if (($column['searchable'] == 'true') && ($column['search']['value'] != '')) {
        $filters[] = $searchColumns[$i] . ' like :filter_' . $i;
        $params['filter_' . $i] = '%' . $column['search']['value'] . '%';
    }
}
if (sizeof($filters) > 0) {
    $searchClause = 'having ' . implode(' and ', $filters);
}

$whereClause = '';
$selectAddColumns = '';
$negate = false;
if (isset($_REQUEST['filter']['invert'])) {
    if ($_REQUEST['filter']['invert'] == 'true') {
        $negate = true;
    }
}
// Filters
// Extension
if (isset($_REQUEST['filter']['extension'])) {
    $extension = $_REQUEST['filter']['extension'];
    if ($extension != '') {
        $whereClause = "where r.id " . ($negate ? "not" : "") . " in (select distinct(reportid) from deviceextensions where name = :filter_extension)";
        $params['filter_extension'] = $extension;
    }
}
// Submitter
if (isset($_REQUEST['filter']['submitter'])) {
    $submitter = $_REQUEST['filter']['submitter'];
    if ($submitter != '') {
        $whereClause = "where r.submitter = :filter_submitter";
        $params['filter_submitter'] = $submitter;
    }
}
// Devicename
if (isset($_REQUEST['filter']['devicename'])) {
    $devicename = $_REQUEST['filter']['devicename'];
    if ($devicename != '') {
        $whereClause = "where (r.devicename = :filter_devicename)";
        $params['filter_devicename'] = $devicename;
    }
}
// Device info value
if (isset($_REQUEST['filter']['deviceinfo']) && isset($_REQUEST['filter']['value'])) {
    $deviceinfoname = $_REQUEST['filter']['deviceinfo'];
    $value = $_REQUEST['filter']['value'];
    if (($deviceinfoname != '') && ($value != '')) {
        $whereClause = "where r.id in (select distinct(reportid) from deviceinfo where name = :filter_deviceinfoname and value = :filter_deviceinfovalue)";
        $params['filter_deviceinfoname'] = $deviceinfoname;
        $params['filter_deviceinfovalue'] = $value;
    }
}

// Platform (CL)
if (isset($_REQUEST['filter']['platformname'])) {
    $platformname = $_REQUEST['filter']['platformname'];
    if ($platformname != '') {
        $whereClause = "where r.id in (select distinct(reportid) from deviceplatforminfo where name = 'CL_PLATFORM_NAME' and value = :filter_platformname)";
        $params['filter_platformname'] = $platformname;
    }
}
// Platform extension
if (isset($_REQUEST['filter']['platformextension'])) {
    $platformextension = $_REQUEST['filter']['platformextension'];
    if ($platformextension != '') {
        $whereClause = "where r.id in (select distinct(reportid) from deviceplatformextensions where name = :filter_platformextension)";
        $params['filter_platformextension'] = $platformextension;
    }
}
// Platform info value
if (isset($_REQUEST['filter']['platforminfo']) && isset($_REQUEST['filter']['value'])) {
    $platforminfoname = $_REQUEST['filter']['platforminfo'];
    $value = $_REQUEST['filter']['value'];
    if (($platforminfoname != '') && ($value != '')) {
        $whereClause = "where r.id in (select distinct(reportid) from deviceplatforminfo where name = :filter_platforminfoname and value = :filter_platforminfovalue)";
        $params['filter_platforminfoname'] = $platforminfoname;
        $params['filter_platforminfovalue'] = $value;
    }
}
// Image format
if (isset($_REQUEST['filter']['flag']) && isset($_REQUEST['filter']['memobjecttype']) && isset($_REQUEST['filter']['channelorder']) && isset($_REQUEST['filter']['channeltype'])) {
	$flag = $_REQUEST['filter']['flag'];
	$mem_object_type = $_REQUEST['filter']['memobjecttype'];
	$channel_order = $_REQUEST['filter']['channelorder'];
	$channel_type = $_REQUEST['filter']['channeltype'];
    $whereClause = "where r.id in (select distinct(reportid) from deviceimageformats where type = :filter_memobjecttype and channelorder = :filter_channelorder and channeltype = :filter_channeltype and $flag = :filter_flag)";
    $params['filter_memobjecttype'] = $mem_object_type;
    $params['filter_channelorder'] = $channel_order;
    $params['filter_channeltype'] = $channel_type;
    $params['filter_flag'] = 1;
}

// Platform (os)
if (isset($_REQUEST['filter']['platform']) && ($_REQUEST['filter']['platform'] != '')) {
    $platform = $_REQUEST['filter']['platform'];
    $ostype = ostype($platform);
    $whereClause .= (($whereClause != '') ? ' and ' : ' where ') . 'ostype = :ostype';
    $params['ostype'] = $ostype;
}

$orderBy = "order by " . $orderByColumn . " " . $orderByDir;

if ($orderByColumn == "api") {
    $orderBy = "order by length(" . $orderByColumn . ") " . $orderByDir . ", " . $orderByColumn . " " . $orderByDir;
}

$sql = "SELECT
        devicename as device,
        max(deviceversion) as deviceversion,
        max(driverversion) as driverversion,
        max(concat(openclversionmajor, '.', openclversionminor)) as openclversion,
        max(r.submissiondate) as submissiondate,
        count(distinct r.id) as reportcount
        from reports r 
        $whereClause
        group by devicename 
        $searchClause
        $orderBy";

$devices = DB::$connection->prepare($sql." ".$paging);
$devices->execute($params);
$display_utils = new DisplayUtils();
if ($devices->rowCount() > 0) {
    foreach ($devices as $device) {
        $url = 'listreports.php?devicename=' . urlencode($device['device']);
        if ($platform !== 'all') {
            $url .= '&platform=' . $platform;
        }
        $data[] = [
            'device' => '<a href="' . $url . '">' . trim(shorten($device['device'], 40)) . '</a>',
            'deviceversion' => shorten($device['deviceversion']),
            'driverversion' => shorten($device['driverversion']),
            'openclversion' => $device['openclversion'],
            'submissiondate' => $device["submissiondate"],
            'reportcount' => $device["reportcount"],
            'compare' => '<center><Button onClick="addToCompare(\''.$device['device'].'\','.($ostype ? $ostype : '').')">Add</Button>'
        ];
    }
}

$stmnt = DB::$connection->prepare($sql);
$stmnt->execute($params);
$totalCount = $stmnt->rowCount();

$results = array(
    "draw" => isset($_REQUEST['draw']) ? intval($_REQUEST['draw']) : 0,
    "recordsTotal" => intval($totalCount),
    "recordsFiltered" => intval($totalCount),
    "data" => $data
);

DB::disconnect();

echo json_encode($results);
