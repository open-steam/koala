<?php
/**
 * steam_date
 *
 * Class definition
 * in sTeam
 *
 * PHP versions 5
 *
 * @package     PHPsTeam
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author      Alexander Roth <aroth@it-roth.de>, Dominik Niehus <nicke@upb.de>
 */

/**
 *
 * @package     PHPsTeam
 */
class steam_date extends steam_object
{

	public function get_type() {
		return CLASS_DATE | CLASS_OBJECT;
	}
	
}
?>