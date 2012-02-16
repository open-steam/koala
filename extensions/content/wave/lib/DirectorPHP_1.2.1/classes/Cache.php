<?php

class DirectorCache extends DirectorWrapper {
	
	function set($key, $expires = '+1 hour') {
		$this->parent->cache_key = $key;
		$this->parent->expires = $expires;
	}
	
	function disable() {
		$this->parent->cache = false;
	}
	
	function get($tail = '') {
		$filename = $this->parent->cache_path . $this->parent->cache_key . DIRECTORY_SEPARATOR . $tail;
		if (file_exists($filename)) {
			$now = time();
			$time = filemtime($filename);
			$expires = strtotime($this->parent->expires, $now);
			$diff = $expires - $now;
			if ($time + $diff < $now) {
				unlink($filename);
				return array();
			} else {
				return file_get_contents($filename);
			}
		} else {
			return array();
		}
	}
	
	function fill($data, $tail) {
		$filename = $this->parent->cache_path . $this->parent->cache_key . DIRECTORY_SEPARATOR . $tail;
		if (!is_dir(dirname($filename))) {
			umask(0);
			if (mkdir(dirname($filename), 0777, true)) {
				// all good
			} else {
				die('DirectorPHP Cache error: The cache directory is not writable. Ensure that the ' . $this->parent->cache_path . ' directory is writable by the web server.');
			}
		}
		if (is_writable(dirname($filename))) {
			file_put_contents($filename, $data);
		} else {
			die('DirectorPHP Cache error: The cache directory is not writable. Ensure that the ' . dirname($filename) . ' directory is writable by the web server.');
		}
	}
}

?>