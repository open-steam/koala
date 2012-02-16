<?php

class DirectorApp extends DirectorWrapper {
	public function version() {
		$response = $this->parent->send('app_version');
		return $response->version;
	}
	
	public function limits() {
		$response = $this->parent->send('app_limits');
		return $response;
	}
	
	public function totals() {
		$response = $this->parent->send('app_totals');
		return $response;
	}
}

?>