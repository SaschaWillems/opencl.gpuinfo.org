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

function shorten($value, $length = 20, $add = '...')
{
	if (strlen($value) >= $length) {
		return substr($value, 0, $length - strlen($add)) . $add;
	}
	return $value;
}

/**
 * Return a sanitized $_GET value to avoid XSS
 */
function GET_sanitized($name)
{
	if (isset($_GET[$name]) ) {
		return sanitize($_GET[$name]);
	};
	return null;
}

function sanitize($value)
{
	return filter_var($value, FILTER_SANITIZE_STRING);
}

function versionToString($version)
{
	// @todo: adopt for OpenCL
	$versionStr = ($version >> 22) . "." . (($version >> 12) & 0x3ff) . "." . ($version & 0xfff);
	return $versionStr;
}

/**
 * Return database os type from platform name
 * 
 * @param string $platform Human readable platform name (Windows, Linux, Android)
 * @return int|null Database mapped os type or null if unknown
 */
function ostype($platform)
{
	switch (strtolower($platform)) {
		case 'windows':
			return 0;
		case 'linux':
			return 1;
		case 'android':
			return 2;
		case 'macos':
			return 3;
		case 'ios':
			return 4;
	}
	return null;
}

/**
 * Return platform name from database os type
 * 
 * @param integer $ostype Database os type
 * @return int|null Numan readable platform name or null if unknown
 */
function platformname($ostype)
{
	switch ($ostype) {
		case 0:
			return 'windows';
		case 1:
			return 'linux';
		case 2:
			return 'android';
		case 3:
			return 'macOS';
		case 4:
			return 'ios';
	}
	return null;
}