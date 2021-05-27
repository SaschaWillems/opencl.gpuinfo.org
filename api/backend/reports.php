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

// @todo: Only allow requests from same host

include '../../database/database.class.php';
include '../../includes/functions.php';

DB::connect();

$data = array();
$params = array();

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
if (isset($_REQUEST['filter']['option'])) {
    if ($_REQUEST['filter']['option'] == 'not') {
        $negate = true;
    }
}
// Filters
// Extension
if (isset($_REQUEST['filter']['extension'])) {
    $extension = $_REQUEST['filter']['extension'];
    if ($extension != '') {
        $whereClause = "where r.id " . ($negate ? "not" : "") . " in (select distinct(reportid) from deviceextensions de join extensions ext on de.extensionid = ext.id where ext.name = :filter_extension)";
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
        $whereClause = "where (r.devicename = :filter_devicename or r.displayname = :filter_devicename)";
        $params['filter_devicename'] = $devicename;
    }
}
// Platform (os)
if (isset($_REQUEST['filter']['platform']) && ($_REQUEST['filter']['platform'] != '')) {
    $platform = $_REQUEST['filter']['platform'];
    $whereClause .= (($whereClause != '') ? ' and ' : ' where ') . 'osname = :osname';
    $params['osname'] = $platform;
}

$orderBy = "order by " . $orderByColumn . " " . $orderByDir;

if ($orderByColumn == "api") {
    $orderBy = "order by length(" . $orderByColumn . ") " . $orderByDir . ", " . $orderByColumn . " " . $orderByDir;
}

$sql = "SELECT
        id,
        devicename,
        deviceversion,
        driverversion,
        concat(openclversionmajor, '.', openclversionminor) as openclversion,
        null as devicetype,
        osname,
        osversion,
        osarchitecture
        from reports r
        " . $whereClause . "        
        " . $searchClause . "
        " . $orderBy;

$devices = DB::$connection->prepare($sql . " " . $paging);
$devices->execute($params);
if ($devices->rowCount() > 0) {
    foreach ($devices as $device) {
        $data[] = [
            'id' => $device['id'],
            'devicename' => '<a href="displayreport.php?id=' . $device['id'] . '">' . $device['devicename'] . '</a>',
            'deviceversion' => $device['deviceversion'],
            'driverversion' => shorten($device['driverversion']),
            'openclversion' => $device['openclversion'],
            'devicetype' => $device['devicetype'],
            'osname' => $device['osname'],
            'osversion' => $device['osversion'],
            'osarchitecture' => $device['osarchitecture'],
            'compare' => '<center><input type="checkbox" name="id[' . $device["id"] . ']"></center>',
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
