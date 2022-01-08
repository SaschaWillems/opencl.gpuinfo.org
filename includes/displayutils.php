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

class DisplayUtils {
    // Map device info enum to display function (only for values that need explicit display transformations)
    private $display_mapping = [
        /* CL 1.0 */
        'CL_DEVICE_VENDOR_ID' => 'displayHexValue',
		'CL_DEVICE_TYPE' => 'displayDeviceType',
		'CL_DEVICE_MAX_WORK_ITEM_SIZES' => 'displayNumberArray',
		'CL_DEVICE_MAX_MEM_ALLOC_SIZE' => 'displayByteSize',
		'CL_DEVICE_IMAGE_SUPPORT' => 'displayBool',
		'CL_DEVICE_MAX_PARAMETER_SIZE' => 'displayByteSize',
		'CL_DEVICE_MIN_DATA_TYPE_ALIGN_SIZE' => 'displayByteSize',
		'CL_DEVICE_SINGLE_FP_CONFIG' => 'displayFloatingPointConfig',
		'CL_DEVICE_GLOBAL_MEM_CACHE_TYPE' => 'displayMemCacheType',
		'CL_DEVICE_GLOBAL_MEM_CACHELINE_SIZE' => 'displayByteSize',
		'CL_DEVICE_GLOBAL_MEM_CACHE_SIZE' => 'displayByteSize',
		'CL_DEVICE_GLOBAL_MEM_SIZE' => 'displayByteSize',
		'CL_DEVICE_MAX_CONSTANT_BUFFER_SIZE' => 'displayByteSize',
		'CL_DEVICE_LOCAL_MEM_TYPE' => 'displayLocalMemType',
		'CL_DEVICE_LOCAL_MEM_SIZE' => 'displayByteSize',
		'CL_DEVICE_ERROR_CORRECTION_SUPPORT' => 'displayBool',
		'CL_DEVICE_ENDIAN_LITTLE' => 'displayBool',
		'CL_DEVICE_COMPILER_AVAILABLE' => 'displayBool',
		'CL_DEVICE_EXECUTION_CAPABILITIES' => 'displayExecCapabilities',

        /* CL 1.1 */
        'CL_DEVICE_HOST_UNIFIED_MEMORY' => 'displayBool',
        'CL_DEVICE_OPENCL_C_VERSION' => 'displayText',

        /* CL 1.2 */
        'CL_DEVICE_LINKER_AVAILABLE' => 'displayBool',
        'CL_DEVICE_BUILT_IN_KERNELS' => 'displaySemicolonSepratedList',
        'CL_DEVICE_PARTITION_PROPERTIES' => 'displayDevicePartitionProperties',
        'CL_DEVICE_PARTITION_AFFINITY_DOMAIN' => 'displayDeviceAffinityDomains',
        'CL_DEVICE_PARTITION_TYPE' => 'displayDevicePartitionProperties',
        'CL_DEVICE_PREFERRED_INTEROP_USER_SYNC' => 'displayBool',
        'CL_DEVICE_PRINTF_BUFFER_SIZE' => 'displayByteSize',

        /* CL 2.0 */
        'CL_DEVICE_MAX_GLOBAL_VARIABLE_SIZE' => 'displayByteSize',
        'CL_DEVICE_QUEUE_ON_DEVICE_PROPERTIES' => 'displayCommandQueueProperties',
        'CL_DEVICE_QUEUE_ON_DEVICE_PREFERRED_SIZE' => 'displayByteSize',
        'CL_DEVICE_QUEUE_ON_DEVICE_MAX_SIZE' =>'displayByteSize',
        'CL_DEVICE_SVM_CAPABILITIES' => 'displayDeviceSvmCapabilities',
        'CL_DEVICE_GLOBAL_VARIABLE_PREFERRED_TOTAL_SIZE' => 'displayByteSize',
        'CL_DEVICE_PIPE_MAX_PACKET_SIZE' => 'displayByteSize',
        'CL_DEVICE_PREFERRED_PLATFORM_ATOMIC_ALIGNMENT' => 'displayByteSize',
        'CL_DEVICE_PREFERRED_GLOBAL_ATOMIC_ALIGNMENT' => 'displayByteSize',
        'CL_DEVICE_PREFERRED_LOCAL_ATOMIC_ALIGNMENT' => 'displayByteSize',

        /* CL 3.0 */
        'CL_DEVICE_NUMERIC_VERSION' => 'displayVersion',
        'CL_DEVICE_ILS_WITH_VERSION' => 'displayNameVersionArray',
        'CL_DEVICE_BUILT_IN_KERNELS_WITH_VERSION' => 'displayNameVersionArray',
        'CL_DEVICE_ATOMIC_MEMORY_CAPABILITIES' => 'displayAtomicCapabilities',
        'CL_DEVICE_ATOMIC_FENCE_CAPABILITIES' => 'displayAtomicCapabilities',
        'CL_DEVICE_NON_UNIFORM_WORK_GROUP_SUPPORT' => 'displayBool',
        'CL_DEVICE_OPENCL_C_ALL_VERSIONS' => 'displayNameVersionArray',
        'CL_DEVICE_WORK_GROUP_COLLECTIVE_FUNCTIONS_SUPPORT' => 'displayBool',
        'CL_DEVICE_GENERIC_ADDRESS_SPACE_SUPPORT' => 'displayBool',
        'CL_DEVICE_OPENCL_C_FEATURES' => 'displayNameVersionArray',
        'CL_DEVICE_DEVICE_ENQUEUE_CAPABILITIES' => 'displayEnqueueCapabilities',
        'CL_DEVICE_PIPE_SUPPORT' => 'displayBool',

        /* Extensions */
        'CL_DEVICE_LUID_VALID_KHR' => 'displaybool',
        'CL_DEVICE_GPU_OVERLAP_NV' => 'displaybool',
        'CL_DEVICE_KERNEL_EXEC_TIMEOUT_NV' => 'displaybool',
        'CL_DEVICE_INTEGRATED_MEMORY_NV' => 'displaybool',
        'CL_DEVICE_EXT_MEM_PADDING_IN_BYTES_QCOM' => 'displayByteSize',
        'CL_DEVICE_PAGE_SIZE_QCOM' => 'displayByteSize',
        'CL_DEVICE_SUB_GROUP_SIZES_INTEL' => 'displayNumberArray',
        'CL_DEVICE_AVC_ME_SUPPORTS_TEXTURE_SAMPLER_USE_INTEL' => 'displayBool',
        'CL_DEVICE_AVC_ME_SUPPORTS_PREEMPTION_INTEL' => 'displayBool',
        'CL_DEVICE_DOUBLE_FP_CONFIG' => 'displayFloatingPointConfig',
        'CL_DEVICE_HALF_FP_CONFIG' => 'displayFloatingPointConfig',
        'CL_DEVICE_COMMAND_BUFFER_CAPABILITIES_KHR' => 'displayCommandBufferCapabilities',
        'CL_DEVICE_SIMULTANEOUS_INTEROPS_INTEL' => 'displaySimultaneousInteropsIntel',

        /* Report meta data */
        'Submitted by' => 'displaySubmitter',
        'Operating system' => 'displayOperatingsystem'
    ];

    /** If true, visualization of flag value types contains all possible flags, with support highlighted using different css classes */
    public $display_all_flags = true;

    function displayNumberArray($value)
    {
        if (substr($value, 0, 2) == 'a:') {
            $value = unserialize($value);
            return '[' . implode(', ', $value) . ']';
        }
        return $value;
    }

    function displayBool($value)
    {
        $text = (intval($value) === 1) ? 'true' : 'false';
        if ($this->display_all_flags) {
            $class = (intval($value) === 1) ? 'supported' : 'unsupported';
            return "<span class='$class'>$text</span>";
        } else {
            return $text;
        }
    }

    function displayByteSize($value)
    {
        return number_format($value).' bytes';
    }

    function displayList($value)
    {
        if (trim($value) == "") {
            return 'none';
        }
        $separator = ';';
        $res = explode($separator, $value);
        if (count($res) > 0) {
            return implode($this->display_all_flags ? '<br/>' : '\n', $res);
        } else {
            return 'none';
        }
    }

    function displayText($value)
    {
        $text = (trim($value) != "") ? $value : 'none';
        if ($this->display_all_flags) {
            if ($text != "none") {
                return $text;
            }
            return "<span class='na'>$text</span>";
        } else {
            return $text;
        }
    }

    function displaySemicolonSepratedList($value)
    {
        $list = explode(';', $value);
        return implode('<br/>', $list);
    }

    function displayHexValue($value)
    {
        return '0x' . strtoupper(dechex($value));
    }

    function displayDeviceType($value)
    {
        // @todo: key value array
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

    function displayNameVersionArray($value)
    {
        if ($value == 0) {
            return "<span class='na'>none</span>";
        }
    }

    function displayMemCacheType($value)
    {
        $mapping = [
            0x0 => 'CL_NONE',
            0x1 => 'CL_READ_ONLY_CACHE',
            0x2 => 'CL_READ_WRITE_CACHE',
        ];
        return $mapping[$value];
    }

    function displayLocalMemType($value)
    {
        $mapping = [
            0x0 => 'CL_NONE',
            0x1 => 'CL_LOCAL',
            0x2 => 'CL_GLOBAL',
        ];
        return $mapping[$value];
    }

    function displayFloatingPointConfig($value)
    {
        $flags = [
            (1 << 0) => 'CL_FP_DENORM',
            (1 << 1) => 'CL_FP_INF_NAN',
            (1 << 2) => 'CL_FP_ROUND_TO_NEAREST',
            (1 << 3) => 'CL_FP_ROUND_TO_ZERO',
            (1 << 4) => 'CL_FP_ROUND_TO_INF',
            (1 << 5) => 'CL_FP_FMA',
            (1 << 6) => 'CL_FP_SOFT_FLOAT',
            (1 << 7) => 'CL_FP_CORRECTLY_ROUNDED_DIVIDE_SQRT',
        ];
        $res = $this->getFlags($flags, $value);
        return implode($this->display_all_flags ? '<br/>' : '\n', $res);
    }

    //

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
            0x10F7 => 'CL_MEM_OBJECT_PIPE',
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
            0x10B8 => "CL_INTENSITY",
            0x10B9 => "CL_LUMINANCE",
            0x10BA => "CL_Rx",
            0x10BB => "CL_RGx",
            0x10BC => "CL_RGBx",
            0x10BD => "CL_DEPTH",
            0x10BE => "CL_DEPTH_STENCIL",
            0x10BF => "CL_sRGB",
            0x10C0 => "CL_sRGBx",
            0x10C1 => "CL_sRGBA",
            0x10C2 => "CL_sBGRA",
            0x10C3 => "CL_ABGR",
            0x4076 => "CL_YUYV_INTEL",
            0x4077 => "CL_UYVY_INTEL",
            0x4078 => "CL_YVYU_INTEL",
            0x4079 => "CL_VYUY_INTEL",
            0x410E => "CL_NV12_INTEL"
        ];
        if (!key_exists($value, $cl_channel_orders)) {
            return '0x'.dechex($value);
        }
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

    function displayAtomicCapabilities($value)
    {
        $flags = [
            (1 << 0) => 'CL_DEVICE_ATOMIC_ORDER_RELAXED',
            (1 << 1) => 'CL_DEVICE_ATOMIC_ORDER_ACQ_REL',
            (1 << 2) => 'CL_DEVICE_ATOMIC_ORDER_SEQ_CST',
            (1 << 3) => 'CL_DEVICE_ATOMIC_SCOPE_WORK_ITEM',
            (1 << 4) => 'CL_DEVICE_ATOMIC_SCOPE_WORK_GROUP',
            (1 << 5) => 'CL_DEVICE_ATOMIC_SCOPE_DEVICE',
            (1 << 6) => 'CL_DEVICE_ATOMIC_SCOPE_ALL_DEVICES',
        ];
        $res = $this->getFlags($flags, $value);
        return implode($this->display_all_flags ? '<br/>' : '\n', $res);
    }

    function displayExecCapabilities($value)
    {
        $flags = [
            (1 << 0) => 'CL_EXEC_KERNEL',
            (1 << 1) => 'CL_EXEC_NATIVE_KERNEL',
        ];
        $res = $this->getFlags($flags, $value);
        return implode($this->display_all_flags ? '<br/>' : '\n', $res);
    }

    function displayEnqueueCapabilities($value)
    {
        $flags = [
            (1 << 0) => 'CL_DEVICE_QUEUE_SUPPORTED',
            (1 << 1) => 'CL_DEVICE_QUEUE_REPLACEABLE_DEFAULT',
        ];
        $res = $this->getFlags($flags, $value);
        return implode($this->display_all_flags ? '<br/>' : '\n', $res);
    }

    function displayCommandQueueProperties($value)
    {
        $flags = [
            (1 << 0) => 'CL_QUEUE_OUT_OF_ORDER_EXEC_MODE_ENABLE',
            (1 << 1) => 'CL_QUEUE_PROFILING_ENABLE',
        ];
        $res = $this->getFlags($flags, $value);
        return implode($this->display_all_flags ? '<br/>' : '\n', $res);
    }

    function displayDeviceSvmCapabilities($value)
    {
        $flags = [
            (1 << 0) => 'CL_DEVICE_SVM_COARSE_GRAIN_BUFFER',
            (1 << 1) => 'CL_DEVICE_SVM_FINE_GRAIN_BUFFER',
            (1 << 2) => 'CL_DEVICE_SVM_FINE_GRAIN_SYSTEM',
            (1 << 3) => 'CL_DEVICE_SVM_ATOMICS',
        ];
        $res = $this->getFlags($flags, $value);
        return implode($this->display_all_flags ? '<br/>' : '\n', $res);
    }

    function displayDevicePartitionProperties($value)
    {
        $text = (trim($value) != "") ? $value : 'none';
        if ($this->display_all_flags) {
            if ($text != "none") {
                return $text;
            }
            return "<span class='na'>$text</span>";
        } else {
            return $text;
        }
    }

    function displayDeviceAffinityDomains($value)
    {
        $flags = [
            (1 << 0) => 'CL_DEVICE_AFFINITY_DOMAIN_NUMA',
            (1 << 1) => 'CL_DEVICE_AFFINITY_DOMAIN_L4_CACHE',
            (1 << 2) => 'CL_DEVICE_AFFINITY_DOMAIN_L3_CACHE',
            (1 << 3) => 'CL_DEVICE_AFFINITY_DOMAIN_L2_CACHE',
            (1 << 4) => 'CL_DEVICE_AFFINITY_DOMAIN_L1_CACHE',
            (1 << 5) => 'CL_DEVICE_AFFINITY_DOMAIN_NEXT_PARTITIONABLE',
        ];
        $res = $this->getFlags($flags, $value);
        return implode($this->display_all_flags ? '<br/>' : '\n', $res);
    }

    function displayCommandQueueCapabilitiesIntel($value)
    {
        if ($value == 0) {
            return 'CL_QUEUE_DEFAULT_CAPABILITIES_INTEL';
        }
        $flags = [
            (1 << 0) => 'CL_QUEUE_CAPABILITY_CREATE_SINGLE_QUEUE_EVENTS_INTEL',
            (1 << 1) => 'CL_QUEUE_CAPABILITY_CREATE_CROSS_QUEUE_EVENTS_INTEL',
            (1 << 2) => 'CL_QUEUE_CAPABILITY_SINGLE_QUEUE_EVENT_WAIT_LIST_INTEL',
            (1 << 3) => 'CL_QUEUE_CAPABILITY_CROSS_QUEUE_EVENT_WAIT_LIST_INTEL',
            (1 << 8) => 'CL_QUEUE_CAPABILITY_TRANSFER_BUFFER_INTEL',
            (1 << 9) => 'CL_QUEUE_CAPABILITY_TRANSFER_BUFFER_RECT_INTEL',
            (1 << 10) => 'CL_QUEUE_CAPABILITY_MAP_BUFFER_INTEL',
            (1 << 11) => 'CL_QUEUE_CAPABILITY_FILL_BUFFER_INTEL',
            (1 << 12) => 'CL_QUEUE_CAPABILITY_TRANSFER_IMAGE_INTEL',
            (1 << 13) => 'CL_QUEUE_CAPABILITY_MAP_IMAGE_INTEL',
            (1 << 14) => 'CL_QUEUE_CAPABILITY_FILL_IMAGE_INTEL',
            (1 << 15) => 'CL_QUEUE_CAPABILITY_TRANSFER_BUFFER_IMAGE_INTEL',
            (1 << 16) => 'CL_QUEUE_CAPABILITY_TRANSFER_IMAGE_BUFFER_INTEL',
            (1 << 24) => 'CL_QUEUE_CAPABILITY_MARKER_INTEL',
            (1 << 25) => 'CL_QUEUE_CAPABILITY_BARRIER_INTEL',
            (1 << 26)  => 'CL_QUEUE_CAPABILITY_KERNEL_INTEL',
        ];
        $res = $this->getFlags($flags, $value);
        return implode($this->display_all_flags ? '<br/>' : '\n', $res);
    }

    function displayQueueFamilyPropertiesIntel($name, $detail, $value)
    {
        $displayValue = null;
        switch(strtolower($detail)) {
            case 'capabilities':
                $displayValue = $this->displayCommandQueueCapabilitiesIntel($value);
                break;
            case 'properties':
                $displayValue = $this->displayCommandQueueProperties($value);
                break;
            default:
                $displayValue = $value;
        }
        if ($displayValue) {
            return "$name - $detail:<div class='deviceinfo-detail-detail'>$displayValue</div>";
        } else {
            return null;
        }
    }

    function displayCommandBufferCapabilities($value)
    {
        $flags = [
            (1 << 0) => 'CL_COMMAND_BUFFER_CAPABILITY_KERNEL_PRINTF_KHR',
            (1 << 1) => 'CL_COMMAND_BUFFER_CAPABILITY_DEVICE_SIDE_ENQUEUE_KHR',
            (1 << 2) => 'CL_COMMAND_BUFFER_CAPABILITY_SIMULTANEOUS_USE_KHR',
            (1 << 3) => 'CL_COMMAND_BUFFER_CAPABILITY_OUT_OF_ORDER_KHR',
        ];
        $res = $this->getFlags($flags, $value);
        return implode($this->display_all_flags ? '<br/>' : '\n', $res);        

    }

    function displaySimultaneousInteropsIntel($value)
    {
        // @todo: Values reported by the client application don't seem to match extenion spec
        if (substr($value, 0, 2) == 'a:') {
            $value = unserialize($value);
            return '[' . implode(', ', $value) . ']';
        }
        return $value;
    }

    function displaySubmitter($value)
    {
        return "<a href=\"listreports.php?submitter=$value\">$value</a>";
    }

    function displayOperatingsystem($value)
    {
        return ucfirst($value);
    }

    //

    function getFlags($flag_list, $flag_value)
    {
        $res = [];
        foreach ($flag_list as $flag => $value) {
            if ($this->display_all_flags) {
                $class = ($flag & $flag_value) ? "supported" : "na";
                if ($this->display_all_flags) {
                    $res[] = "<span class='$class'>".$value."</span>";
                }
            } else {
                if ($flag & $flag_value) {
                    $res[] = $value;
                }
            }
        }
        return $res;
    }

    //

    public function getDisplayValue($value_name, $value)
    {
        if (key_exists($value_name, $this->display_mapping)) {
            $value = call_user_func('DisplayUtils::'.$this->display_mapping[$value_name], $value);
            if ($value != '') {
                return $value;
            }
        }
        return $value;
    }

    /** Contains the display mapping functions for device info detail values */
    public function getDetailDisplayValue($deviceInfoName, $name, $detail, $value)
    {
        switch ($deviceInfoName) {
            case 'CL_DEVICE_BUILT_IN_KERNELS_WITH_VERSION':
            case 'CL_DEVICE_ILS_WITH_VERSION':
            case 'CL_DEVICE_OPENCL_C_ALL_VERSIONS':
            case 'CL_DEVICE_OPENCL_C_FEATURES':
                return $name." ".displayVersion($value).($this->display_all_flags ? '<br/>' : '\n');
                break;
            case 'CL_DEVICE_PCI_BUS_INFO_KHR':
                return "$name: $value<br/>";
                break;
            case 'CL_DEVICE_QUEUE_FAMILY_PROPERTIES_INTEL':
                return $this->displayQueueFamilyPropertiesIntel($name, $detail, $value);
        }
    }
}