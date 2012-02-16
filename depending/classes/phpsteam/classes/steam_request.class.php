<?php
/**
 * steam_request describes the data structure and the methods for
 * encoding/decoding requests to/from the steam server through the
 * COAL-protocol.
 *
 * PHP versions 5
 * 
 * @package 	PHPsTeam
 * @license	http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author	Henrik Beige <hebeige@gmx.de>, Alexander Roth <aroth@it-roth.de>, Dominik Niehus <nicke@upb.de>
 */

/**
 * steam_request describes the data structure and the methods for
 * encoding/decoding requests to/from the steam server through the
 * COAL-protocol.
 *
 * @package     PHPsTeam
 */
class steam_request
{
	//***************************************************************************
	//class variables
	//***************************************************************************

	var $steam_connectorID;

	var $length;
	var $transactionid;
	var $coalcommand;
	var $object;
	// Force Error if application accesses arguments without get method !
	// Sorry for that, but it is hardly needed to ensure correct
	// implementation. Please contact dbuese@upb.de if you need help.
	private $arguments;

	var $length_encoded;
	var $transactionid_encoded;
	var $object_encoded;
	var $arguments_encoded;

	var $command_encoded;

	//***************************************************************************
	//construtor: steam_request()
	//***************************************************************************
	/**
	* function steam_request:
	*
	* @param $pSteamConnector
	* @param $transactionid
	* @param $object
	* @param $arguments
	* @param coalcommand
	*/
	function steam_request( $pSteamConnectorID, $transactionid = 0, $object = 0, $arguments = 0, $coalcommand = COAL_COMMAND)
	{
		if (!is_string($pSteamConnectorID)) throw new ParameterException("pSteamConnectorID", "string");
		//set class variables
		$this->steam_connectorID = $pSteamConnectorID;
		$this->set_transactionid($transactionid);
		$this->set_coalcommand($coalcommand);
		$this->set_object($object);
		$this->set_arguments($arguments);

	} //function steam_request($transactionid, $coalcommand, $object, $arguments)


	//***************************************************************************
	//method: encode()
	//***************************************************************************
	/**
	* function encode:
	*
	* @return
	*/
	function encode()
	{
		//build COAL command
		$command = "\xff" . $this->length_encoded . $this->transactionid_encoded . $this->coalcommand . $this->object_encoded . $this->arguments_encoded;

		return $command;

	} //function encode()


	//***************************************************************************
	//method: encode_data()
	//NOTE: float fehlt
	//***************************************************************************

	/**
	 * Encode object
	 * @param $id object id
	 * @param $class object class
	 */

	function encode_object($id, $class) {
		$data = CMD_TYPE_OBJECT .
		pack("C*", $id >> 24, $id >> 16, $id >> 8, $id) .
		pack("C*", $class >> 24, $class >> 16, $class >> 8, $class);
		return $data; 
	}

	/**
	 * function encode_data:
	 *
	 * @param $object_keys if true, encode integer as object (used is array needs objects as keys => PHP is not able to handle that by itself)
	 * @param $data the data to encode
	 *
	 * @return
	 */
	function encode_data($data, $object_keys = FALSE)
	{
		//encode array/mapping
		if(is_array($data))
		{
			//check if its an  array or mapping
			$array = true;
			$j = 0;
			foreach($data as $key => $value)
			{
				if(gettype($key) != "integer" || !($key === $j))
				{
					$array = false;
					break;
				}
				$j++;
			}


			//build array
			if($array)
			{
				$count = sizeof($data);
				$newdata = CMD_TYPE_ARRAY . pack("C*", $count >> 8 , $count);

				foreach($data as $tmpdata)
				{
					$newdata .= $this->encode_data($tmpdata);
				} //foreach($data as $tmpdata)

			}

			//build mapping
			else
			{
				$object_keys = FALSE;
				if (isset($data["_OBJECT_KEYS"]) && $data["_OBJECT_KEYS"] === "TRUE") {
					$object_keys = TRUE;
					unset($data["_OBJECT_KEYS"]);
				}
				$count = sizeof($data);
				$newdata = CMD_TYPE_MAPPING . pack("C*", $count >> 8, $count);

				foreach($data as $key => $tmpdata)
				{
					$newdata .= $this->encode_data($key, $object_keys);
					$newdata .= $this->encode_data($tmpdata);
				}
			}

		}

		//encode basic types
		else
		{
			$type = gettype($data);
			switch ($type)
			{
				case "boolean":
					$data = ($data)?1:0;
				case "integer":
					if ($object_keys) $newdata = $this->encode_object($data, CLASS_OBJECT);
					else $newdata = CMD_TYPE_INT . pack("C*", $data >> 24, $data >> 16, $data >> 8, $data);
					break;
				case "float":
				case "double":
					$newdata = CMD_TYPE_FLOAT . strrev(pack("f*" ,$data));
					break;
				case "string":
					$len = strlen($data);
					$newdata = CMD_TYPE_STRING . pack("C*", $len >> 24, $len >> 16, $len >> 8, $len) . $data;
					break;
				case "object":
					$newdata = $this->encode_object($data->get_id(), $data->get_type());
					break;
				case "null":
				case "NULL":
					/*
					 Use Integer 0 to represent NULL.
					 TODO: Implement Support of NULL within the Protocol itself.
					 */
					return $this->encode_data(0);
					break;
				default:
					throw new steam_exception( steam_connector::get_instance($this->steam_connectorID)->get_login_user_name(), "Error: Type '$type' is not supported by the COAL protocoll!<br>\n", 120 );
			} //switch
		}

		return $newdata;

	} //function encode_data($data)


	//***************************************************************************
	//method: decode()
	//***************************************************************************
	/**
	* function decode:
	*
	* @param $command
	*
	* @return
	*/
	function decode($command, $flushing = FALSE)
	{
		$this->command_encoded = $command;


		//strip answer of header
		$this->length = hexdec(bin2hex(substr($command, 1, 4)));
		$this->transactionid = hexdec(bin2hex(substr($command, 5, 4)));
		$this->coalcommand = $command[9];

		// AR: NEW STEAM_OBJECT

		$this->object = steam_factory::get_object($this->steam_connectorID, hexdec(bin2hex(substr($command, 10, 4))), hexdec(bin2hex(substr($command, 14, 4))) );

		//get data
		$command = substr($command, 18);
		$this->arguments = $this->decode_data($command);

		// detect if result is an error
		if ( $this->coalcommand == COAL_ERROR ) {
			if ( is_array($this->arguments) )
			$sex = new steam_exception(steam_connector::get_instance($this->steam_connectorID)->get_login_user_name(), "Error during data transfer. COAL_ERROR : args[0]=" . $this->arguments[0] . " args[1]=" . $this->arguments[1], 120);
			else
			$sex = new steam_exception(steam_connector::get_instance($this->steam_connectorID)->get_login_user_name(),  "Error during data transfer", 120 );
			if (!$flushing) throw $sex;
			else return $sex;
		}
		return $this->arguments;
	} //function decode($command)

	function mybin2dec($str) {
		$result = 0;
		$pos = true;
		if (($str[0] & chr(pow(2,7))) == chr(pow(2,7))) {
			$pos = false;
		}
		 
		for($i=3; $i > -1; $i--) {
			$byte = $str[$i];
			for($j=0; $j < 8; $j++) {
				if ($i == 0 && $j == 7) {
					if ($pos) {
						return $result;
					} else {
						return -1 * $result - 1;
					}
				} else {
					if ($pos) {
						(($byte & chr(pow(2,$j))) == chr(pow(2,$j)) ) ? $result += (pow(2,$j) << (8 * (3 - $i))): "";
					} else {
						(($byte & chr(pow(2,$j))) == chr(pow(2,$j)) ) ? "": $result += (pow(2,$j) << (8 * (3 - $i)));
					}
				}
			}
		}
		return $result;
	}

	//***************************************************************************
	//method: decode_data()
	//***************************************************************************
	/**
	* function decode_data
	*
	* @param $command
	*
	* @return
	*/
	function decode_data(&$command)
	{
		//echo "decode_data<br>";
		$typ = $command[0];

		switch ($typ)
		{
			case CMD_TYPE_INT:
				$newdata = $this->mybin2dec(substr($command, 1, 4));
				$command = substr($command, 5);
				break;
			case CMD_TYPE_FLOAT:
				$tmp = unpack("f*", strrev(substr($command, 1 , 4)));
				$newdata = $tmp[1];
				$command = substr($command, 5);
				//echo $newdata . "<br />";
				break;
			case CMD_TYPE_STRING:
				$length = (string) hexdec(bin2hex(substr($command, 1, 4)));
				$newdata = substr($command, 5, $length);
				$command = substr($command, 5 + $length);
				break;
			case CMD_TYPE_OBJECT:
				$newdata = steam_factory::get_object( $this->steam_connectorID, hexdec(bin2hex(substr($command, 1, 4))), hexdec(bin2hex(substr($command, 5, 4))) );
				$command = substr($command, 9);
				break;
			case CMD_TYPE_ARRAY:
				$count = hexdec(bin2hex(substr($command, 1, 2)));
				$command = substr($command, 3);

				if($count <= 0)
				$newdata = array();
				else
				for($i = 0; $i < $count; $i++)
				{
					$value = $this->decode_data($command);
					$newdata[$i] = $value;
				}; //for($i = 0; $i < $count; $i++)

				break;
			case CMD_TYPE_MAPPING:
				$count = hexdec(bin2hex(substr($command, 1, 2)));
				$command = substr($command, 3);

				if($count <= 0)
				$newdata = array();
				else
				for($i = 0; $i < $count; $i++)
				{
					$key = $this->decode_data($command);
					$value = $this->decode_data($command);

					if(is_object($key))
					$newdata[$key->get_id()] = $value; //TODO: $newdata[(string)$key] = $value;
					else if(is_array($key))
					$newdata[] = $value;
					else
					$newdata[$key] = $value;
				}; //for($i = 0; $i < $count; $i++)

				break;
			case CMD_TYPE_PROGRAM:
				$newdata = "type program not yet implemented";
				break;
			case CMD_TYPE_TIME:
				$newdata = (int) hexdec(bin2hex(substr($command, 1, 4)));
				$command = substr($command, 5);
				break;
			case CMD_TYPE_FUNCTION:
				$length = hexdec(bin2hex(substr($command, 1, 4)));
				$fname = substr($command, 13, $length-8);
				$newdata = new steam_function( $fname );
				$command = substr($command, 5 + $length);
				break;
			default:
				throw new steam_exception(steam_connector::get_instance($this->steam_connectorID)->get_login_user_name(), "COAL support in PHP not yet implemented for object type=" . $typ . " command=" . $command, 120 );
				$newdata = "COAL support in PHP not yet implemented for object type=" . $typ . " command=" . $command;
				break;
		} //switch ($typ)

		return $newdata;

	} //function decode_data($command)


	//***************************************************************************
	//methods to set variable and encode (same time)
	//***************************************************************************

	/**
	 *function set_transactionid:
	 *
	 * @param $transactionid
	 *
	 */
	function set_transactionid($transactionid)
	{
		$this->transactionid = $transactionid;
		$this->transactionid_encoded = pack("C*", $transactionid >> 24, $transactionid >> 16, $transactionid >> 8 , $transactionid);
	} //function set_transactionid($transactionid)

	/**
	 * function set_coalcommand
	 *
	 * @param $coalcommand
	 */
	function set_coalcommand($coalcommand)
	{
		$this->coalcommand = $coalcommand;
	} //function set_coalcommand($coalcommand)

	/**
	 * function set_object
	 *
	 * @param $object
	 */
	function set_object($object)
	{
		if( !is_object($object) )
		{
			$this->object = steam_factory::get_object( $this->steam_connectorID );
			$this->object_encoded = "\x00\x00\x00\x00\x00\x00\x00\x00";
		}
		else
		{
			$this->object = $object;
			$id = $object->get_id();
			$type = $object->get_type();
			$this->object_encoded =  pack("C*", $id >> 24, $id >> 16, $id >> 8, $id ) .
			pack("C*", $type >> 24, $type >> 16, $type >> 8, $type );
		}
	} //function set_object($object)

	/**
	 * function set_arguments
	 *
	 * @param $arguments
	 */
	function set_arguments($arguments)
	{
		$this->arguments = $arguments;
		$this->arguments_encoded = $this->encode_data($arguments);

		$this->length = strlen($this->arguments_encoded) + 18;
		$this->length_encoded = pack("C*", $this->length >> 24, $this->length >> 16, $this->length >> 8, $this->length);
	} //function set_arguments($arguments)


	//***************************************************************************
	//methods to get variable status
	//***************************************************************************
	/**
	* function is_error:
	*
	* @return
	*/
	function is_error() { return ($this->coalcommand == COAL_ERROR); }

	/**
	 * function access_granted:
	 *
	 * @return
	 */
	function access_granted() { return ($this->arguments[1] == "Access denied !");}

	/**
	 * function get_transactionid:
	 *
	 * @return
	 */
	function get_transactionid()
	{
		return $this->transactionid;
	} //function get_transactionid()

	/**
	 * function get_coalcommand
	 *
	 * @return
	 */
	function get_coalcommand()
	{
		return $this->coalcommand;
	} //function get_coalcommand()

	/**
	 *function get_object
	 *
	 * @return
	 */
	function get_object()
	{
		return $this->object;
	} //function get_object()

	/**
	 * function get_arguments
	 *
	 * @return
	 */
	function get_arguments()
	{
		return $this->arguments;
	} //function get_arguments()

}; //class steam_request

?>