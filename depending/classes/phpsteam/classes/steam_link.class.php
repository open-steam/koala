<?php

/**
 * steam_link
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
class steam_link extends steam_object
{
	
	public function get_type() {
		return CLASS_LINK | CLASS_OBJECT;
	}
	
	/**
	 * function get_link_object:
	 *
	 * @param $pBuffer
	 *
	 * @return
	 */
	public function get_link_object($pBuffer = FALSE)
	{
		return $this->steam_command(
		$this,
			"get_link_object",
		array(),
		$pBuffer
		);
	}

	/**
	 * function get_source_object:
	 *
	 * @return
	 */
	public function get_source_object()
	{
		if ( $source_object = $this->get_link_object() )
		{
			while ( ! ( ( $source_object->get_type() & CLASS_DOCEXTERN ) == CLASS_DOCEXTERN ) && ( $source_object->get_type() & CLASS_LINK ) == CLASS_LINK )
			{
				$new_source_object = $source_object->get_link_object();
				if ( ! is_subclass_of( $new_source_object, "steam_object" ) )
				{
					break;
				}
				$source_object = $new_source_object;
			}
			return $source_object;
		}
		else
		{
			return $this;
		}
	}

	/**
	 * function set_link_object:
	 *
	 * @param $pSource
	 * @param $pBuffer
 	* 
 	* @return 
 	*/
	public function set_link_object( $pSource, $pBuffer = 0 )
	{
		return $this->steam_command(
			$this,
			"set_link_object",
			array( $pSource ),
			$pBuffer
		);
	}

}

?>