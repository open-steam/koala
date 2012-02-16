<?php
	interface Response_old{
	
		public function setStatus($status);
		public function addHeader($name, $value);
		public function write($data);
		public function flush();
	}
?>