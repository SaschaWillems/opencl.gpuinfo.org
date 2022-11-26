<?php

/**
 *
 * Vulkan hardware capability database server implementation
 *	
 * Copyright (C) 2016-2022 by Sascha Willems (www.saschawillems.de)
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

// Writes and reads devices to compare to the server session

session_start();
header("HTTP/1.1 200 OK");

$action = $_POST['action'];
$devicename = $_POST['devicename'];
$ostype = $_POST['ostype'];
$identifier = 'opencl_compare_devices';

switch($action) {
    case 'add':
        if ((!is_array($_SESSION[$identifier])) || (!array_key_exists($devicename, $_SESSION[$identifier]))) {
            $_SESSION[$identifier][$devicename] = $ostype !== '' ? $ostype : null;
        }
        break;
    case 'remove':
        if ((!is_array($_SESSION[$identifier])) || (array_key_exists($devicename, $_SESSION[$identifier]))) {
            unset($_SESSION[$identifier][$devicename]);
        }
        break;
    case 'clear':      
        $_SESSION[$identifier] = [];
        break;
}

$response = [];
if (is_array($_SESSION[$identifier])) {
    foreach ($_SESSION[$identifier] as $key => $value) {
        $response[] = [
            "name" => $key,
            "ostype" => $value,
        ];
    }
}

$json = json_encode($response);
echo $json;