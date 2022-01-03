/** 		
 *
 * OpenCL hardware capability database MySQL database structure
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

CREATE TABLE `deviceextensions` (
  `reportid` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `version` int(11) DEFAULT NULL,
  PRIMARY KEY (`reportid`,`name`),
  CONSTRAINT `deviceextensions_FK` FOREIGN KEY (`reportid`) REFERENCES `reports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `deviceimageformats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reportid` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `channelorder` int(11) NOT NULL,
  `channeltype` int(11) NOT NULL,
  `flags` int(11) NOT NULL,
  `CL_MEM_READ_WRITE` int(11) DEFAULT NULL,
  `CL_MEM_WRITE_ONLY` int(11) DEFAULT NULL,
  `CL_MEM_READ_ONLY` int(11) DEFAULT NULL,
  `CL_MEM_KERNEL_READ_AND_WRITE` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`,`reportid`),
  KEY `deviceimageformats_FK` (`reportid`),
  CONSTRAINT `deviceimageformats_FK` FOREIGN KEY (`reportid`) REFERENCES `reports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9905 DEFAULT CHARSET=latin1 COMMENT=' ';

CREATE TABLE `deviceinfo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reportid` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `enumvalue` int(11) DEFAULT '0',
  `extension` varchar(255) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `index_extension` (`extension`),
  KEY `deviceinfo_FK` (`reportid`),
  CONSTRAINT `deviceinfo_FK` FOREIGN KEY (`reportid`) REFERENCES `reports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5086 DEFAULT CHARSET=latin1;

CREATE TABLE `deviceinfodetails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `deviceinfoid` int(11) NOT NULL,
  `reportid` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  `detail` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`,`deviceinfoid`,`reportid`),
  KEY `deviceinfodetails_FK_idx` (`deviceinfoid`,`reportid`),
  CONSTRAINT `deviceinfodetails_FK` FOREIGN KEY (`deviceinfoid`) REFERENCES `deviceinfo` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=305 DEFAULT CHARSET=latin1;

CREATE TABLE `deviceplatformextensions` (
  `reportid` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `version` int(11) DEFAULT NULL,
  PRIMARY KEY (`reportid`,`name`),
  CONSTRAINT `deviceplatformextensions_FK` FOREIGN KEY (`reportid`) REFERENCES `reports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `deviceplatforminfo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reportid` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `enumvalue` int(11) DEFAULT '0',
  `extension` varchar(255) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `index_deviceplatform_extension` (`extension`),
  KEY `deviceplatforminfo_FK` (`reportid`),
  CONSTRAINT `deviceplatforminfo_FK` FOREIGN KEY (`reportid`) REFERENCES `reports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=248 DEFAULT CHARSET=latin1;

CREATE TABLE `reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `devicename` varchar(255) NOT NULL,
  `deviceversion` varchar(255) NOT NULL,
  `devicetype` int(11) DEFAULT NULL,
  `driverversion` varchar(255) NOT NULL,
  `openclversionmajor` int(11) NOT NULL,
  `openclversionminor` int(11) NOT NULL,
  `osname` varchar(255) NOT NULL,
  `osversion` varchar(64) NOT NULL,
  `osarchitecture` tinytext NOT NULL,
  `reportversion` char(16) DEFAULT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `submitter` varchar(255) DEFAULT NULL,
  `submissiondate` datetime DEFAULT CURRENT_TIMESTAMP,
  `counter` int(11) DEFAULT '0',
  `appversion` char(16) DEFAULT NULL,
  `deviceidentifier` varchar(1024) DEFAULT NULL,
  `ostype` int(11) DEFAULT NULL,
  `gpuname` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `index_devicename` (`devicename`),
  KEY `index_deviceversion` (`deviceversion`),
  KEY `index_driverversion` (`driverversion`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=latin1;

CREATE TABLE `reportsjson` (
  `reportid` int(11) NOT NULL,
  `json` mediumtext NOT NULL,
  PRIMARY KEY (`reportid`),
  KEY `reportid` (`reportid`),
  CONSTRAINT `reportsjson_FK` FOREIGN KEY (`reportid`) REFERENCES `reports` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
