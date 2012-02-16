<?php

class steam_document_test extends UnitTestCase {
	
    function setUp() {
        $GLOBALS["STEAM"] = new steam_connector("192.168.154.139", 1900, "root", "steam123");
        $this->assertTrue($GLOBALS["STEAM"]->get_login_status());
    }
    
    function tearDown() {
        $GLOBALS["STEAM"]->disconnect();
    }
	
	function test_set_content() {
	}
	
}
?>
