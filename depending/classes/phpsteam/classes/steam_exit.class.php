<?php

/**
 * steam_exit
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
class steam_exit extends steam_link
{
	
	public function get_type() {
		return CLASS_EXIT | CLASS_LINK | CLASS_OBJECT;
	}
	
	/**
	 * function get_exit:
	 *
	 * @param $pBuffer
	 *
	 * @return
	 */
	public function get_exit($pBuffer = 0)
	{
		return $this->steam_command(
		$this,
			"get_exit",
		array(),
		$pBuffer
		);
	}
}
?>