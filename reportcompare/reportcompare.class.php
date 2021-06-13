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

class ReportCompareDeviceInfo
{
    public $version = null;
    public $device_description = null;
    public $platform = null;
    public $reportid = null;
}

class ReportCompareData
{
    public $captions;
    public $data;
    public $count;
}

class ReportCompare
{

    private $header_column_names = ['device', 'driverversion', 'apiversion', 'os'];
    private $report_column_names = ['id', 'submissiondate', 'submitter', 'devicename', 'driverversion', 'apiversion', 'counter', 'osarchitecture', 'osname', 'osversion', 'description', 'version', 'headerversion', 'displayname', 'ostype', 'internalid', 'reportid'];    

    public $report_ids = [];
    public $report_id_list = null;
    public $report_count = 0;
    public $device_infos = [];

    function __construct($reportids)
    {
        foreach ($reportids as $id) {
            $this->report_ids[] = intval($id);
        }
        sort($this->report_ids);
        $this->report_count = count($reportids);
        // Imploded report id list to be used as database query parameter
        $this->report_id_list = implode(",", $this->report_ids);
    }

    public function fetchData()
    {
        // Fetch descriptions for devices to be compared
        try {
            $sql = 
                "SELECT 
                    id, 
                    devicename, 
                    deviceversion,
                    devicetype,                
                    driverversion, 
                    openclversionmajor, 
                    openclversionminor, 
                    osname, 
                    osversion, 
                    osarchitecture,
                    ostype,
                    reportversion, 
                    submitter,
                    comment,
                    submissiondate,
                    counter,
                    appversion
                FROM reports        
                WHERE id in ($this->report_id_list)";
            $stmnt = DB::$connection->prepare($sql);
            $stmnt->execute();
        } catch (PDOException $e) {
            die("Could not fetch report data!");
        }
        foreach ($stmnt->fetchAll(PDO::FETCH_ASSOC) as $device) {
            $device_info = new ReportCompareDeviceInfo;
            $device_info->version = $device['reportversion'];
            $device_info->device_description = $device['devicename'];
            $device_info->platform = $device['osname'];
            $device_info->reportid = $device['id'];
            $this->device_infos[] = $device_info;
        }
    }

    public function isHeaderColumn($column_name)
    {
        return in_array($column_name, $this->header_column_names);
    }

    /**
     * Insert table header with device names into the current table
     */
    public function insertTableHeader($caption, $grouping_column = false, $device_info = true)
    {
        echo "<thead><tr><th>$caption</th>";
        if ($grouping_column) {
            echo "<th></th>";
        }
        if ($device_info) {
            foreach ($this->device_infos as $device_info) {
                echo "<th>";
                echo $device_info->device_description;
                echo "<br>";
                echo ucfirst($device_info->platform);
                echo "</th>";
            }
        } else {
            foreach ($this->device_infos as $device_info) {
                echo "<th>&nbsp;</th>";
            }
        }
        echo "</thead><tbody>";
    }

    public function fetchDeviceInfo(&$device_info_values, &$report_data)
    {
        try {
            // Get a list of all device info values for the selected reports
            try {
                $stmnt = DB::$connection->prepare("SELECT distinct name from deviceinfo where reportid in ($this->report_id_list)");
                $stmnt->execute();
                $device_info_values = $stmnt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                die('Could not fetch device info vaues for compare!');
                DB::disconnect();
            }

            // Get extended features for each selected report into an array 
            foreach ($this->report_ids as $reportid) {
                try {
                    $stmnt = DB::$connection->prepare("SELECT name, value, extension from deviceinfo where reportid = :reportid");
                    $stmnt->execute(['reportid' => $reportid]);
                    $report_data[] = $stmnt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    die("Could not fetch device info values for compare!");
                }
            }
            return true;
        } catch (Throwable $e) {
            return false;
        }        
    }

    public function fetchDeviceExtensions(&$device_extension_list, &$report_data)
    {
        try {
            // Get a list of all device info values for the selected reports
            try {
                $stmnt = DB::$connection->prepare("SELECT distinct name from deviceextensions where reportid in ($this->report_id_list)");
                $stmnt->execute();
                $device_extension_list = $stmnt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                die('Could not fetch device info vaues for compare!');
                DB::disconnect();
            }

            // Get extended features for each selected report into an array 
            foreach ($this->report_ids as $reportid) {
                try {
                    $stmnt = DB::$connection->prepare("SELECT name from deviceextensions where reportid = :reportid");
                    $stmnt->execute(['reportid' => $reportid]);
                    $report_data[] = $stmnt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    die("Could not fetch device info values for compare!");
                }
            }
            return true;
        } catch (Throwable $e) {
            return false;
        }        
    }        

    // HTML builder functions

    public function insterDiffIcon($key, $display_icon)
    {
        if ($display_icon) {
            return "<span class='glyphicon glyphicon-transfer' title='This value differs across reports' style='padding-right: 5px;'></span>$key";
        } else {
            return $key;
        }
    }    

    public function beginTable($id)
    {
        echo "<table id='$id' width='100%' class='table table-striped table-bordered table-hover'>";
    }

    public function endTable()
    {
        echo "</tbody></table>";
    }

    public function beginTab($id, $active = false)
    {
        echo "<div id='$id' class='tab-pane fade reportdiv " . ($active ? "in active" : "") . "'>";
    }

    public function endTab()
    {
        echo "</div>";
    }
}
