<?php

/**
 * steam_trashbin
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
class steam_trashbin extends steam_container
{
	public function get_type() {
		return CLASS_TRASHBIN | CLASS_CONTAINER | CLASS_OBJECT;
	}
	
	/**
	* function empty_trashbin:
	*
	* This function empties the trashbin.
	*
	* Example:
	* <code>
	* $trashbin->empty_trashbin()
	* </code>
	*
	* @param $pBuffer 0 = send command now, 1 = buffer command
	*
	*/
	public function empty_trashbin($pBuffer = 0 )
	{
		$this->steam_command(
		$this,
				"empty",
		array(),
		$pBuffer
		);
	}
}

?>