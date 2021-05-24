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

class ReportFlags
{
    public $has_update_history = false;
}

class ReportApiVersion
{
    public $major = null;
    public $minor = null;
}

class ReportInfo
{
    public $version = null;
    public $device_description = null;
    public $platform = null;
}

class Report
{
    public $id = null;
    public ReportApiVersion $apiversion;
    public ReportInfo $info;
    public ReportFlags $flags;

    function __construct($reportid)
    {
        $this->id = $reportid;
        $this->apiversion = new ReportApiVersion;
        $this->flags = new ReportFlags;
        $this->info = new ReportInfo;
    }

    public function exists()
    {
        DB::connect();
        $stmnt = DB::$connection->prepare("SELECT 1 from reports where id = :reportid LIMIT 1");
        $stmnt->execute([':reportid' => $this->id]);
        $result = $stmnt->fetchColumn();
        DB::disconnect();
        return $result;
    }

    public function fetchData()
    {
        DB::connect();
        $sql = 
            "SELECT 
                id, 
                devicename, 
                deviceversion, 
                driverversion, 
                openclversionmajor, 
                openclversionminor, 
                osname, 
                osversion, 
                osarchitecture, 
                reportversion, 
                description, 
                submitter, 
                submissiondate, 
                counter, 
                appversion
            FROM reports        
            WHERE id = :reportid";
        $stmnt = DB::$connection->prepare($sql);
        $stmnt->execute([':reportid' => $this->id]);
        $row = $stmnt->fetch(PDO::FETCH_ASSOC);
        $this->info->version = $row['reportversion'];
        $this->info->device_description = $row['devicename'];
        $this->apiversion->major = $row['openclversionmajor'];
        $this->apiversion->minor = $row['openclversionminor'];
        $this->info->platform = $row['osname'];
        $this->flags->has_update_history = false;
        DB::disconnect();
    }

    public function fetchDeviceInfo()
    {
        try {
            $sql = "SELECT name, value from deviceinfo where extension = \"\" and reportid = :reportid order by id asc";
            $stmnt = DB::$connection->prepare($sql);
            $stmnt->execute([":reportid" => $this->id]);
            $result = $stmnt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function fetchExtensions()
    {
        try {
            $sql = "SELECT name, version from deviceextensions where reportid = :reportid order by name asc";
            $stmnt = DB::$connection->prepare($sql);
            $stmnt->execute([":reportid" => $this->id]);
            $result = $stmnt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function fetchUpdateHistory()
    {
        try {
            $sql = "SELECT date, submitter, log, reportversion from reportupdatehistory where reportid = :reportid order by id desc";
            $stmnt = DB::$connection->prepare($sql);
            $stmnt->execute([":reportid" => $this->id]);
            $result = $stmnt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function fetchPlatformInfo()
    {
        try {
            $sql = "SELECT name, value from deviceplatforminfo where extension = \"\" and reportid = :reportid order by id asc";
            $stmnt = DB::$connection->prepare($sql);
            $stmnt->execute([":reportid" => $this->id]);
            $result = $stmnt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Throwable $e) {
            return null;
        }
    }

    public function fetchPlatformExtensions()
    {
        try {
            $sql = "SELECT name, version from deviceplatformextensions where reportid = :reportid order by name asc";
            $stmnt = DB::$connection->prepare($sql);
            $stmnt->execute([":reportid" => $this->id]);
            $result = $stmnt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Throwable $e) {
            return null;
        }
    }    
}
