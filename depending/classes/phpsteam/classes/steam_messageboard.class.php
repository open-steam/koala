<?php

/**
 * steam_messageboard
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
class steam_messageboard extends steam_object
{

	private $current_thread;
	private $current_article;

	public function get_type() {
		return CLASS_MESSAGEBOARD | CLASS_OBJECT;
	}
	
	/**
	 * function create_entry:
	 *
	 * @param $pEntryName
	 * @param $pDescription
	 *
	 * @return
	 */
	protected function create_entry( $pEntryName, $pDescription )
	{
		$new_annotation = steam_factory::create_textdoc(
		$this->steam_connectorID,
		$pEntryName,
		$pDescription
		);

		$this->add_annotation( $new_annotation );
		// set acquiring
		$new_annotation->set_acquire($this);

		return $new_annotation;
	}

	/**
	 * function add_thread:
	 * This function creates a new thread in the messageboard
	 *
	 * @param string $pThreadName the name of the thread
	 * @param string $pTreadDescription content of the thread
	 *
	 * @return steam_document the thread object
	 */
	public function add_thread( $pThreadName, $pThreadDescription)
	{
		return $this->create_entry(
		$pThreadName,
		$pThreadDescription
		);
	}

	/**
	 * function add_article:
	 *
	 * @param $pSubject
	 * @param $pContent
	 *
	 * @return
	 */
	public function add_article( $pSubject, $pContent )
	{
		return $this->create_entry(
		$pSubject,
		$pContent
		);
	}

	/**
	 * function delete_thread:
	 *
	 * @param $pThread
	 *
	 * @return
	 */
	public function delete_thread( $pThread )
	{
		$objects_to_delete[] = $pThread;
		// hier weitermachen
		return $pThread->delete();
	}

	/**
	 * function delete_article:
	 *
	 * @param $pArticle
	 *
	 * @return
	 */
	public function delete_article( $pArticle )
	{
		return $pArticle->delete();
	}

}

?>