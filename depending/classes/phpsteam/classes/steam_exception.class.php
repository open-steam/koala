<?php
/**
 * steam_exception
 *
 * PHP versions 5
 *
 * @package     PHPsTeam
 * @license     http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author      Alexander Roth <aroth@it-roth.de>, Daniel Büse <dbuese@upb.de>, Dominik Niehus <nicke@upb.de>
 */

/**
 *
 * @package     PHPsTeam
 */
class steam_exception extends Exception {
	public $backtrace;
	public $user;
	public $allow_backtrace;
	private $security_issue = "Backtrace of this error is not available due to security issues.";

	/**
	 * constructor of steam_exception:
	 *
	 * @param $pUser
	 * @param $pMessage
	 * @param $pCode
	 */
	public function __construct( $pUser = "Anonymous", $pMessage = FALSE, $pCode = FALSE, $pallow_backtrace = TRUE )
	{
		if ( ! $pMessage )
		{
			$this->message = "non-specified error";
		}
		if ( ! $pCode )
		{
			$this->code = 0;
		}
		$this->user = $pUser;
		$this->allow_backtrace = $pallow_backtrace;
		if ($pallow_backtrace) $this->backtrace = debug_backtrace();
		else $this->backtrace = $this->security_issue;
		parent::__construct( $pMessage, $pCode );
	}

	/**
	 * function get_backtrace:
	 *
	 * @return
	 */
	public function get_backtrace()
	{
		return var_dump( $this->backtrace );
	}

	/**
	 * function get_message:
	 *
	 * @return
	 */
	public function get_message()
	{
		return $this->message;
	}

	/**
	 * function get_code:
	 *
	 * @return
	 */
	public function get_code()
	{
		return $this->code;
	}

	/**
	 * function get_user:
	 *
	 * @return
	 */
	public function get_user()
	{
		return $this->user;
	}


	/**
	 * override super method  to get control of log output if exception is not
	 * catched
	 */
	function __toString() {
		if ($this->allow_backtrace) return $this->get_message();
		else return $this->security_issue;
	}

}
?>