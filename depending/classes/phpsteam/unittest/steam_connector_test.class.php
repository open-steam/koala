<?php

class steam_connector_test extends UnitTestCase {
	
	function test_get_login_status() {
		$steam_connector = new steam_connector("192.168.154.139", 1900, "root", "steam123");
		$this->assertTrue($steam_connector->get_login_status(), "checking get_login_status on success");
		
		$steam_connector = new steam_connector("192.168.154.139", 1900, "root", "steam1235");
		$this->assertFalse($steam_connector->get_login_status(), "checking get_login_status on fail");
	}
	
}
?>