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

/**
 * Array with colors to use for charts
 */
$chart_colors = [ '#3366CC', '#DC3912', '#FF9900', '#109618', '#990099', '#0099C6', '#DD4477', '#66AA00', '#B82E2E', '#316395', '#994499', '#22AA99', '#AAAA11', '#6633CC', '#E67300', '#8B0707', '#651067', '#329262', '#5574A6', '#3B3EAC', '#B77322', '#16D620', '#B91383', '#F4359E', '#9C5935', '#A9C413', '#2A778D', '#668D1C', '#BEA413', '#0C5922', '#743411'];

/**
 * Draw an Apex.JS chart
 */
function drawChart($labels, $series, $colors) {
    echo "
        var options = {
            chart: {
                type: 'pie',
                height: '360px',
                width: '360px',
                animations: {
                    enabled: false,
                },
            },
            legend: {
                show: false,
            },
            colors: ['".implode("','", $colors)."'],
            labels: ['".str_replace('\\n', '<br/>', implode("','", $labels))."'],
            series: [".implode(',', $series)."]
        }
        var chart = new ApexCharts(document.getElementById('chart'), options)
        chart.render()
    ";
}