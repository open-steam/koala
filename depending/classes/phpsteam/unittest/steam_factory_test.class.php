<?php

class steam_factory_test extends UnitTestCase {
	
    function setUp() {
        $GLOBALS["STEAM"] = new steam_connector("192.168.154.139", 1900, "root", "steam123");
        $this->assertTrue($GLOBALS["STEAM"]->get_login_status());
    }
    
    function tearDown() {
        $GLOBALS["STEAM"]->disconnect();
    }
	
	function test_groupname_to_object() {
		$steam_group = steam_factory::groupname_to_object($GLOBALS["STEAM"]->get_id(), "steam");
		$this->assertTrue(is_object($steam_group));
		$this->assertTrue($steam_group instanceof steam_group);
		$this->assertTrue($steam_group->get_name() === "sTeam");	
	}
	
}
?>