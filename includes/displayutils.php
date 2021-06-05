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

function displayVersion($version)
{
    $CL_VERSION_MINOR_BITS = 10;
    $CL_VERSION_PATCH_BITS = 12;

    $CL_VERSION_MINOR_MASK = ((1 << $CL_VERSION_MINOR_BITS) - 1);
    $CL_VERSION_PATCH_MASK = ((1 << $CL_VERSION_PATCH_BITS) - 1);

    $major = (($version) >> ($CL_VERSION_MINOR_BITS + $CL_VERSION_PATCH_BITS));
    $minor = ((($version) >> $CL_VERSION_PATCH_BITS) & $CL_VERSION_MINOR_MASK);
    $patch = (($version) & $CL_VERSION_PATCH_MASK);

    return "$major.$minor.$patch";
}

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

/*
 * Contains the display mapping functions for device info detail values
 */
function getDetailDisplayValue($deviceInfoName, $detailName, $detailValue)
{
    switch ($deviceInfoName) {
        case 'CL_DEVICE_BUILT_IN_KERNELS_WITH_VERSION':
        case 'CL_DEVICE_ILS_WITH_VERSION':
        case 'CL_DEVICE_OPENCL_C_ALL_VERSIONS':
        case 'CL_DEVICE_OPENCL_C_FEATURES':
            return $detailName." ".displayVersion($detailValue);
            break;
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

function displayMemObjectType($value)
{
    $cl_mem_object_types = [
        0x10F0 => 'CL_MEM_OBJECT_BUFFER',
        0x10F1 => 'CL_MEM_OBJECT_IMAGE2D',
        0x10F2 => 'CL_MEM_OBJECT_IMAGE3D',
        0x10F3 => 'CL_MEM_OBJECT_IMAGE2D_ARRAY',
        0x10F4 => 'CL_MEM_OBJECT_IMAGE1D',
        0x10F5 => 'CL_MEM_OBJECT_IMAGE1D_ARRAY',
        0x10F6 => 'CL_MEM_OBJECT_IMAGE1D_BUFFER',
        0x10F7=> 'CL_MEM_OBJECT_PIPE',
    ];
    return $cl_mem_object_types[$value];
}

function displayChannelOrder($value)
{
    $cl_channel_orders = [
        0x10B0 => "CL_R",
        0x10B1 => "CL_A",
        0x10B2 => "CL_RG",
        0x10B3 => "CL_RA",
        0x10B4 => "CL_RGB",
        0x10B5 => "CL_RGBA",
        0x10B6 => "CL_BGRA",
        0x10B7 => "CL_ARGB",
        0x10B8=> "CL_INTENSITY",
        0x10B9 => "CL_LUMINANCE",
        0x10BA => "CL_Rx",
        0x10BB=> "CL_RGx",
        0x10BC => "CL_RGBx",
        0x10BD=> "CL_DEPTH",
        0x10BE => "CL_DEPTH_STENCIL",
        0x10BF => "CL_sRGB",
        0x10C0 => "CL_sRGBx",
        0x10C1 => "CL_sRGBA",
        0x10C2 => "CL_sBGRA",
        0x10C3 => "CL_ABGR",
    ];
    return $cl_channel_orders[$value];
}

function displayChannelType($value)
{
    $cl_channel_types = [
        0x10D0 => "CL_SNORM_INT8",
        0x10D1 => "CL_SNORM_INT16",
        0x10D2 => "CL_UNORM_INT8",
        0x10D3 => "CL_UNORM_INT16",
        0x10D4 => "CL_UNORM_SHORT_565",
        0x10D5 => "CL_UNORM_SHORT_555",
        0x10D6 => "CL_UNORM_INT_101010",
        0x10D7 => "CL_SIGNED_INT8",
        0x10D8 => "CL_SIGNED_INT16",
        0x10D9 => "CL_SIGNED_INT32",
        0x10DA => "CL_UNSIGNED_INT8",
        0x10DB => "CL_UNSIGNED_INT16",
        0x10DC => "CL_UNSIGNED_INT32",
        0x10DD => "CL_HALF_FLOAT",
        0x10DE => "CL_FLOAT",
        0x10DF => "CL_UNORM_INT24",
        0x10E0 => "CL_UNORM_INT_101010_2",
    ];
    return $cl_channel_types[$value];
}