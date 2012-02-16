<?php

/**
 * steam_room
 *
 * PHP versions 5
 *
 * @package     PHPsTeam
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author      Alexander Roth <aroth@it-roth.de>, Dominik Niehus <nicke@upb.de>
 *
 */

/**
 *
 * @package     PHPsTeam
 */
class steam_room extends steam_container
{
	
	public function get_type() {
		return CLASS_ROOM | CLASS_CONTAINER | CLASS_OBJECT;
	}
	
	/**
	 * function get_visitors:
	 *
	 * Returns the user visiting this room
	 * @return mixed Array of steam_users
	 */
	public function get_visitors()
	{
		return $this->get_inventory(
		CLASS_USER
		);
	}


}

?>