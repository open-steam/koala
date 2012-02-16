<?php
	interface Request_old{
	
		public function getParameterNames();
		public function issetParameter($name);
		public function getParameter($name);
		public function getHeader($name);
	}
?>