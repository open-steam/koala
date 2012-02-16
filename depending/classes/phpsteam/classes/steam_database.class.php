<?php

/**
 * steam_database
 *
 * PHP versions 5
 *
 * @package     PHPsTeam
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author      Dominik Niehus <nicke@upb.de>
 *
 */

/**
 *
 * @package     PHPsTeam
 */
class steam_database
{
	/**
	 * Unique id for this object inside the virtual space, which is
	 * assigned by a sTeam-server.
	 */
	protected $id;

	/**
	 * ID of steam_connector. Connection to sTeam-server
	 */
	public $steam_connectorID;
	
	public function __construct( $pSteamConnectorID, $pID = "0")
	{
		$s = debug_backtrace();
		if ($s[1]['class'] !== "steam_factory") {
			error_log("phpsteam error: direct construtor-call not allowed ({$s[1]['class']})");
			throw new Exception("direct construtor-call not allowed ({$s[1]['class']})");
		}
		if (!is_string($pSteamConnectorID)) throw new ParameterException( "pSteamConnectorID", "string" );
		$this->id 	= $pID;
		$this->steam_connectorID = $pSteamConnectorID;
	}
	
	public function get_type() {
		return CLASS_DATABASE;
	}
}

?>