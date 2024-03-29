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
$searchColumns = ['id'];
array_push($searchColumns, 'devicename', 'deviceversion', 'driverversion', 'openclversion', 'devicetype', 'osname', 'osversion', 'osarchitecture');
$exactcolumns = ['devicetype'];

// Per-column filtering
$filters = array();
for ($i = 0; $i < count($_REQUEST['columns']); $i++) {
    $column = $_REQUEST['columns'][$i];
    if (($column['searchable'] == 'true') && ($column['search']['value'] != '')) {
        if (in_array($searchColumns[$i], $exactcolumns)) {
            $filters[] = $searchColumns[$i] . ' = :filter_' . $i;
            if ($searchColumns[$i] == 'devicetype') {
                $params['filter_' . $i] = devicetypeid($column['search']['value']);
            } else {
                $params['filter_' . $i] = $column['search']['value'];
            }
        } else {
            $filters[] = $searchColumns[$i] . ' like :filter_' . $i;
            $params['filter_' . $i] = '%' . $column['search']['value'] . '%';
        }
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
    // Values are stored as details for this device info value
    if (isset($_REQUEST['filter']['detailvalue'])) {
        $deviceinfodetailname = $_REQUEST['filter']['detailvalue'];
        if (true) {
            $whereClause = '
            where r.id in (select reportid from (select di.reportid, group_concat(did.value order by did.value asc) as `values` from deviceinfodetails did left join deviceinfo di on did.deviceinfoid = di.id and did.reportid = di.reportid 
            where di.name = :filter_deviceinfoname and did.name= :filter_deviceinfodetailname
            group by reportid
            having `values` = (:filter_values)) tab)';
            $params['filter_deviceinfoname'] = $deviceinfoname;
            $params['filter_deviceinfodetailname'] = $deviceinfodetailname;
            $params['filter_values'] = $value;
        }
    } else {
        if (($deviceinfoname != '') && ($value != '')) {
            $whereClause = "where r.id in (select distinct(reportid) from deviceinfo where name = :filter_deviceinfoname and value = :filter_deviceinfovalue)";
            $params['filter_deviceinfoname'] = $deviceinfoname;
            $params['filter_deviceinfovalue'] = $value;
        }
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
        id,
        devicename,
        deviceversion,
        devicetype,
        driverversion,
        concat(openclversionmajor, '.', openclversionminor) as openclversion,
        osname,
        osversion,
        osarchitecture
        from reports r      
        " . $whereClause . "        
        " . $searchClause . "
        " . $orderBy;

$devices = DB::$connection->prepare($sql . " " . $paging);
$devices->execute($params);
$display_utils = new DisplayUtils();
if ($devices->rowCount() > 0) {
    foreach ($devices as $device) {
        $data[] = [
            'id' => $device['id'],
            'devicename' => '<a href="displayreport.php?id=' . $device['id'] . '">' . trim(shorten($device['devicename'], 40)) . '</a>',
            'deviceversion' => shorten($device['deviceversion']),
            'driverversion' => shorten($device['driverversion']),
            'openclversion' => $device['openclversion'],
            'devicetype' => $display_utils->displayDeviceType($device['devicetype']),
            'osname' => $device['osname'],
            'osversion' => $device['osversion'],
            'osarchitecture' => $device['osarchitecture'],
            'compare' => '<center><Button onClick="addToCompare('.$device['id'].',\''.$device['devicename'].'\')">Add</Button>',
        ];
    }
}

$filteredCount = 0;
$stmnt = DB::$connection->prepare("select count(*) from reports");
$stmnt->execute();
$totalCount = $stmnt->fetchColumn();

$filteredCount = $totalCount;
if (($searchClause != '') or ($whereClause != '')) {
    $stmnt = DB::$connection->prepare($sql);
    $stmnt->execute($params);
    $filteredCount = $stmnt->rowCount();
}

$results = array(
    "draw" => isset($_REQUEST['draw']) ? intval($_REQUEST['draw']) : 0,
    "recordsTotal" => intval($totalCount),
    "recordsFiltered" => intval($filteredCount),
    "data" => $data
);

DB::disconnect();

echo json_encode($results);
