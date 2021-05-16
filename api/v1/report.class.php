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

class Report {
	public $data = null;
	public function fromJson($json) {	
		$this->data = json_decode($json, true);
	}

	public function getDeviceInfoValue($name) {
		foreach ($this->data['device']['info'] as $info) {
			if (strcasecmp($info['name'], $name) == 0) {
				return $info['value'];
			}
		}
		return null;
	}

	public function getEnvironmentValue($name) {
		if (array_key_exists($name, $this->data['environment'])) {
			return $this->data['environment'][$name];
		}
		return null;
	}

	public function getOpenCLValue($name) {
		if (array_key_exists($name, $this->data['device']['opencl'])) {
			return $this->data['device']['opencl'][$name];
		}
		return null;
	}

	public function deviceInfo() {
		return $this->data['device']['info'];
	}

	public function deviceExtensions() {
		return $this->data['device']['extensions'];
	}

	public function platformInfo() {
		return $this->data['platform']['info'];
	}

	public function platformExtensions() {
		return $this->data['platform']['extensions'];
	}

}
