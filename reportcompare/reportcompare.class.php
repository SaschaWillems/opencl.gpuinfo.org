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

class ReportCompareDeviceInfo
{
    public $version = null;
    public $device_description = null;
    public $driver_version = null;
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
            $device_info->driver_version = $device['driverversion'];
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
                echo shorten($device_info->device_description, 30);
                echo "<br>";
                echo shorten($device_info->driver_version, 30);
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

    // Device information

    public function fetchDeviceInfo(&$device_info_values, &$report_data)
    {
        try {
            // Get a list of all device info values for the selected reports
            try {
                $stmnt = DB::$connection->prepare("SELECT distinct name from deviceinfo where reportid in ($this->report_id_list)");
                $stmnt->execute();
                $device_info_values = $stmnt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                die('Could not fetch device info values for compare!');
                DB::disconnect();
            }

            // Get device info values for each selected report into an array 
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

    public function fetchDeviceInfoDetails(&$report_data)
    {
        try {
            // Get device info detail values for each selected report into an array 
            foreach ($this->report_ids as $reportid) {
                try {
                    $stmnt = DB::$connection->prepare("SELECT di.name as deviceinfo, did.name, did.detail, did.value from deviceinfodetails did join deviceinfo di on did.deviceinfoid = di.id where di.reportid = :reportid");
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
            // Get a list of all device extension values for the selected reports
            try {
                $stmnt = DB::$connection->prepare("SELECT distinct name from deviceextensions where reportid in ($this->report_id_list)");
                $stmnt->execute();
                $device_extension_list = $stmnt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                die('Could not fetch device extension values for compare!');
                DB::disconnect();
            }

            // Get extensions for each selected report into an array 
            foreach ($this->report_ids as $reportid) {
                try {
                    $stmnt = DB::$connection->prepare("SELECT name from deviceextensions where reportid = :reportid");
                    $stmnt->execute(['reportid' => $reportid]);
                    $report_data[] = $stmnt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    die("Could not fetch device extension values for compare!");
                }
            }
            return true;
        } catch (Throwable $e) {
            return false;
        }        
    }

    public function fetchDeviceImageformats(&$device_format_values, &$report_data)
    {
        try {
            // Get a list of all device image format combinations for the selected reports
            try {
                $stmnt = DB::$connection->prepare("SELECT distinct type, channelorder, channeltype from deviceimageformats where reportid in ($this->report_id_list)");
                $stmnt->execute();
                $device_format_values = $stmnt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                die('Could not fetch device image foramats for compare!');
                DB::disconnect();
            }

            // Get image formats for each selected report into an array 
            foreach ($this->report_ids as $reportid) {
                try {
                    $stmnt = DB::$connection->prepare("SELECT * from deviceimageformats where reportid = :reportid");
                    $stmnt->execute(['reportid' => $reportid]);
                    $report_data[] = $stmnt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    die("Could not fetch device image formats for compare!");
                }
            }
            return true;
        } catch (Throwable $e) {
            return false;
        }        
    }

    public function getImageFormatInfo($report_data, $image_format)
    {
        foreach($report_data as $format) {
            if (($format[0]['type'] == $image_format['type']) && ($format[0]['channelorder'] == $image_format['channelorder']) && ($format[0]['channeltype'] == $image_format['channeltype'])) {
                return $format;
            }
        }
        return null;
    }

    // Platform information

    public function fetchPlatformInfo(&$platform_info_values, &$report_data)
    {
        try {
            // Get a list of all platform info values for the selected reports
            try {
                $stmnt = DB::$connection->prepare("SELECT distinct name from deviceplatforminfo where reportid in ($this->report_id_list)");
                $stmnt->execute();
                $platform_info_values = $stmnt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                die('Could not fetch platform info values for compare!');
                DB::disconnect();
            }

            // Get platform info values for each selected report into an array 
            foreach ($this->report_ids as $reportid) {
                try {
                    $stmnt = DB::$connection->prepare("SELECT name, value, extension from deviceplatforminfo where reportid = :reportid");
                    $stmnt->execute(['reportid' => $reportid]);
                    $report_data[] = $stmnt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    die("Could not fetch platform info values for compare!");
                }
            }
            return true;
        } catch (Throwable $e) {
            return false;
        }        
    }    

    public function fetchPlatformExtensions(&$platform_extension_list, &$report_data)
    {
        try {
            // Get a list of all platform extensions for the selected reports
            try {
                $stmnt = DB::$connection->prepare("SELECT distinct name from deviceplatformextensions where reportid in ($this->report_id_list)");
                $stmnt->execute();
                $platform_extension_list = $stmnt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                die('Could not fetch platform extension vaues for compare!');
                DB::disconnect();
            }

            // Get platform extensions for each selected report into an array 
            foreach ($this->report_ids as $reportid) {
                try {
                    $stmnt = DB::$connection->prepare("SELECT name from deviceplatformextensions where reportid = :reportid");
                    $stmnt->execute(['reportid' => $reportid]);
                    $report_data[] = $stmnt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
                } catch (PDOException $e) {
                    die("Could not fetch platform extension values for compare!");
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

    // Static functions

    /** Get the id for the latest report for a given device and (optional) platform */
    static public function getLatestReport($devicename, $os) 
    {
        try {
            $osfilter = null;
            $params = ['devicename' => $devicename];
            if ($os && (trim($os) !== '')) {
                $params = ['ostype' => ostype($os)];
                $osfilter = "and ostype = :ostype";
            }
            $sql = "SELECT id from reports where devicename = :devicename $osfilter order by driverversion desc limit 1";
            $stmnt = DB::$connection->prepare($sql);
            $stmnt->execute($params);
            $row = $stmnt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                return $row['id'];
            } else {
                return null;
            }
        } catch (PDOException $e) {
            die("Could not get latest report for $devicename");
        }
    }
}
