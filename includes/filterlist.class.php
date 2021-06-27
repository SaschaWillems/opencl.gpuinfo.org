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

 /** Manages filters from url parameters */
class Filterlist {

public const DeviceInfo = 0;
public const PlatformInfo = 1;

public $filters = [];

function __construct($filters) {
    foreach ($filters as $filter ) {
        $this->addFilter($filter);
    }
}

function addFilter($name) {
    $value = GET_sanitized($name);
    if (($value !== null) && (trim($value) != '')) {
        $this->filters[$name] = $value;
    }
}

function getFilter($name) {
    if (key_exists($name, $this->filters)) {
        $value = $this->filters[$name];
        if (trim($value) != '') {
            return $value;
        }
    }
    return null;
}

function hasFilter($name) {
    return (key_exists($name, $this->filters));
}

function hasFilters() {
    return (count($this->filters) > 0);
}

function belongsToExtension($name, $target, &$ext) {
    $table = null;
    switch ($target) {
        case self::DeviceInfo:
            $table = 'deviceinfo';
            break;
        case self::PlatformInfo:
            $table = 'deviceplatforminfo';
            break;
    }
    $res = false;
    if ($table) {
        DB::connect();
        $stmnt = DB::$connection->prepare("SELECT extension FROM $table where name = :name");
        $stmnt->execute([':name' => $name]);
        $row = $stmnt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $ext = $row['extension'];
            $res = true;
        }
        DB::disconnect();
    }
    return $res;
}
}
