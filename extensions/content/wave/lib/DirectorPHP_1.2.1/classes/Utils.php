<?php

class DirectorUtils extends DirectorWrapper {
	
	function is_video($fn) {
		if (eregi('\.flv|\.mov|\.mp4|\.m4a|\.m4v|\.3gp|\.3g2', $fn)) {
			return true;
		} else {
			return false;
		}
	}
	
	function is_image($filename) {
		if (!$this->is_video($filename)) {
			return true;
		} else {
			return false;
		}
	}
	
	function truncate($string, $limit = 40, $tail = '...') {
		if (strlen($string) > $limit) {
			$string = substr($string, 0, $limit) . $tail;
		}
		return $string;
	}
	
	public function convert_line_breaks($string, $br = true) {
		$string = preg_replace("/(\r\n|\n|\r)/", "\n", $string);
		$string = preg_replace("/\n\n+/", "\n\n", $string);
		$string = preg_replace('/\n?(.+?)(\n\n|\z)/s', "<p>$1</p>\n", $string);
		if ($br) {
			$string = preg_replace('|(?<!</p>)\s*\n|', "<br />\n", $string);
		}
		return $string;
	}
	
	private function decode($obj) {
		foreach(get_object_vars($obj) as $key => $val) {
			if (count($val) == 0) {
				$obj->{$key} = urldecode($val);
			} else {
				foreach($obj->{$key}[0] as $key2 => $val2) {
					if (count($val2) == 0) {
						$obj->{$key}->{$key2} = urldecode($val2);
					} else {
						foreach($obj->{$key}->{$key2}[0] as $key3 => $val3) {
							@$obj->{$key}->{$key2}->{$key3} = urldecode($val3);
						}
					}
				}
			}
		}
		return $obj;
	}
	
	public function dodecode($response) {
		if (isset($response->album)) {
			foreach($response->album->contents[0] as $c) {
				$c = $this->decode($c);
			}
		} else if (isset($response->contents)) {
			foreach($response->contents[0] as $c) {
				$c = $this->decode($c);
			}
		} else if (isset($response->content)) {
			$c = $this->decode($response->content);
		} else if (isset($response->gallery->albums)) {
			foreach($response->gallery->albums[0] as $album) {
				foreach($album->contents[0] as $c) {
					$c = $this->decode($c);
				}
			}
		}
		return $response;
	}
}

?>