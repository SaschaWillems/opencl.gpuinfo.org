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
  * Contains the display mapping functions from the client app (ported to PHP)
  */

  function getDisplayValue($deviceInfoName, $value)
  {
      switch ($deviceInfoName) {
        case 'CL_DEVICE_IMAGE_SUPPORT': 
        case 'CL_DEVICE_ERROR_CORRECTION_SUPPORT':
        case 'CL_DEVICE_ENDIAN_LITTLE':
        case 'CL_DEVICE_COMPILER_AVAILABLE':
        case 'CL_DEVICE_LINKER_AVAILABLE':
        case 'CL_DEVICE_PREFERRED_INTEROP_USER_SYNC':
        case 'CL_DEVICE_NON_UNIFORM_WORK_GROUP_SUPPORT':
        case 'CL_DEVICE_WORK_GROUP_COLLECTIVE_FUNCTIONS_SUPPORT':
        case 'CL_DEVICE_GENERIC_ADDRESS_SPACE_SUPPORT':
        case 'CL_DEVICE_PIPE_SUPPORT':
        case 'CL_DEVICE_LUID_VALID_KHR':
        case 'CL_DEVICE_GPU_OVERLAP_NV':
        case 'CL_DEVICE_KERNEL_EXEC_TIMEOUT_NV':
        case 'CL_DEVICE_INTEGRATED_MEMORY_NV':
            return displayBool($value);
            break;
        case 'CL_DEVICE_TYPE':
            return displayDeviceType($value);
            break;
        case 'CL_DEVICE_MAX_WORK_ITEM_SIZES':
        case 'CL_DEVICE_SUB_GROUP_SIZES_INTEL':
            return displayNumberArray($value);
            break;
        default:
            return $value;
      }
  }

  function displayBool($value)
  {
    $class = (intval($value) === 1) ? 'supported' : 'unsupported';
    $text = (intval($value) === 1) ? 'true' : 'false';
    return "<span class='$class'>$text</span>";
  }

  function displayDeviceType($value)
  {
      switch (intval($value))
      {
        case 1:
            return 'DEFAULT';
            break;
        case 2:
            return 'CPU';
            break;
        case 4:
            return 'GPU';
            break;
        case 8:
            return 'ACCELERATOR';
            break;
        case 16:
            return 'CUSTOM';
            break;
      default: 
        return 'unknown';
      }
  }

  function displayNumberArray($value)
  {
    if (substr($value, 0, 2) == 'a:') {
        $value = unserialize($value);
        return '[' . implode(', ', $value) . ']';
    } else {
        return "Unserialize error";
    }
  }