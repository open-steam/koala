<?php

require_once(PATH_CLASSES . "PEAR/SOAP/Client.php");
//include_once("WSDL.php");
include_once(PATH_ETC . "error.codes.php");
include_once(PATH_LIB . "format_handling.inc.php");

if (!defined("PATH_CERTS")) define("PATH_CERTS", PATH_ETC . "ssl/certs/");

class paul_soap
{
        private function get_soap_wsdl_client($url)
        {
 			// Hier kann man das wsdl Cashing von PHP ausschalten
			// ini_set("soap.wsdl_cache_enabled", "0");

			$wsdl = new SOAP_WSDL( $url );
			$soap = $wsdl->getProxy();

			if ($url == PIA_URL)
			{
				$soap->setOpt('curl', CURLOPT_CAINFO, PATH_CERTS . 'telekom-root-ca-2.pem');
				$soap->setOpt('curl', CURLOPT_SSL_VERIFYPEER, 1 );
				$soap->setOpt('curl', CURLOPT_SSL_VERIFYHOST, 2 );
			}
			else
			{
				$soap->setOpt('curl', CURLOPT_CAINFO, PATH_CERTS . 'paul_certs.pem');
				$soap->setOpt('curl', CURLOPT_SSL_VERIFYPEER, 0 ); // must be 1 in productive environment
				$soap->setOpt('curl', CURLOPT_SSL_VERIFYHOST, 0 ); // must be 2 in productive envoronment
			}

			return $soap;
        }


        public function get_person_no_by_uid($uid)
        {
			$auth = array();
			$auth[] = new SOAP_Value('{http://auth.imt/xsd/}password', 'string', PIA_SOAP_PASSWORD);
			$auth[] = new SOAP_Value('{http://auth.imt/xsd/}username', 'string', PIA_SOAP_USER);

    		$soap_wsdl_client = $this->get_soap_wsdl_client( PIA_URL );
			$result = $soap_wsdl_client->getPaulIdByUid( $auth, $uid );

			// wrong password or wrong password or service error
			if ($result instanceof Soap_Fault) throw new Exception("Soap Error", E_SOAP_SERVICE_ERROR);
			// invalid uid as input -> result NULL
			if ($result == null) throw new Exception("Invalid Uid", E_SOAP_INVALID_INPUT);

			if ( !ereg( "^0*$", $result ) ) return $result;
			else throw new Exception( "Invalid Response Data", E_SOAP_INVALID_RESPONSE_DATA );
        }


        public function get_uid_by_person_no($no)
        {
        	if ( !ereg( "^[0-9]{1,8}$", $no ) ) throw new Exception("Invalid Person_No", E_SOAP_INVALID_INPUT);

			$auth = array();
			$auth[] = new SOAP_Value('{http://auth.imt/xsd/}password', 'string', PIA_SOAP_PASSWORD);
			$auth[] = new SOAP_Value('{http://auth.imt/xsd/}username', 'string', PIA_SOAP_USER);

    		$soap_wsdl_client = $this->get_soap_wsdl_client( PIA_URL );
			$result = $soap_wsdl_client->getUidByPaulId( $auth, $no );

			// wrong password or wrong password or service error
			if ($result instanceof Soap_Fault) throw new Exception("Soap Error", E_SOAP_SERVICE_ERROR);
			// invalid person_no as input -> result NULL
			if ($result == null) throw new Exception("Invalid Person_No", E_SOAP_INVALID_INPUT);

			if ( !ereg( "^0*$", $result ) ) return $result;
			else throw new Exception( "Invalid Response Data", E_SOAP_INVALID_RESPONSE_DATA );
        }


        public function get_person_id_by_person_no($no)
        {
        	if ( !ereg( "^[0-9]{1,8}$", $no ) ) throw new Exception("Invalid Person_No", E_SOAP_INVALID_INPUT);

        	$soap_wsdl_client = $this->get_soap_wsdl_client( PAUL_URL );
			$params = array( 'secret_key' => PAUL_SOAP_PASSWORD, 'person_no' => $no );
			$result = $soap_wsdl_client->ws_paul_keys( $params );

			// service error
			if ($result instanceof Soap_Fault) throw new Exception("Soap Error", E_SOAP_SERVICE_ERROR);

			if ( $this->checkValidity($result, 'person_id') ) return $result['person_id'];
        }


        public function get_person_no_by_person_id($id)
        {
        	if ( !ereg( "^[0-9]{15}$", $id ) ) throw new Exception("Invalid Person_Id", E_SOAP_INVALID_INPUT);

        	$soap_wsdl_client = $this->get_soap_wsdl_client( PAUL_URL );
			$params = array( 'secret_key' => PAUL_SOAP_PASSWORD, 'person_id' => $id );
			$result = $soap_wsdl_client->ws_paul_keys( $params );

			// service error
			if ($result instanceof Soap_Fault) throw new Exception("Soap Error", E_SOAP_SERVICE_ERROR);

			if ( $this->checkValidity($result, 'person_no') ) return $result['person_no'];
        }


		public function get_all_courses_by_person($person_id)
		{
			if ( !ereg( "^[0-9]{15}$", $person_id ) ) throw new Exception("Invalid Person_Id", E_SOAP_INVALID_INPUT);

			$ids = array();
			$soap_wsdl_client = $this->get_soap_wsdl_client( PAUL_URL );
			$params = array( 'secret_key' => PAUL_SOAP_PASSWORD, 'person_id' => $person_id ,'semester_id' => SEMESTER_ID );
			$result = $soap_wsdl_client->ws_all_courses_person_semester( $params );

			// service error
			if ($result instanceof Soap_Fault) throw new Exception("Soap Error", E_SOAP_SERVICE_ERROR);

			if ( !is_array($result) )
			{
       			if ( $result == -1 )
       			throw new Exception( "Invalid person_id", E_SOAP_INVALID_INPUT );

       			else if ( $result == -9 )
       			throw new Exception( "Invalid Password", E_SOAP_INVALID_PASSWORD );
			}
			else if ( $result['error_code'] == 0 )
			{
				$result = $result['course_id'];

				if ( is_array($result) )
				{
					// more than one course
					$this->rec_array_check($result, $ids);
					return $ids;
				}
				else
				{
					// only one course
					if ( ereg( "^[0-9]{15}$", $result ) && !ereg( "^0*$", $result ) ) return array($result);
				}

			}
			throw new Exception( "Invalid Response Data", E_SOAP_INVALID_RESPONSE_DATA );
		}


		private function rec_array_check($element, &$finalArray)
		{
			if( is_array($element) )
			{
				foreach ($element as $e) $this->rec_array_check($e, $finalArray);
			}
			else $finalArray[] = $element;
		}


        public function get_course_information($course_id)
        {
        	if ( !ereg( "^[0-9]{15}$", $course_id ) ) throw new Exception("Invalid Course_Id", E_SOAP_INVALID_INPUT);

        	$soap_wsdl_client = $this->get_soap_wsdl_client( PAUL_URL );
			$params =  array( 'secret_key' => PAUL_SOAP_PASSWORD, 'course_id' => $course_id );
			$result = $soap_wsdl_client->ws_course_details( $params );

			// wrong password or wrong password or service error
			if ($result instanceof Soap_Fault) throw new Exception("Soap Error", E_SOAP_SERVICE_ERROR);

			if ( $this->checkValidity($result, 'course_name_german') ) return $result;
       	}


       	public function get_participants($course_id)
       	{
       		if ( !ereg( "^[0-9]{15}$", $course_id ) ) throw new Exception("Invalid Course_Id", E_SOAP_INVALID_INPUT);

			$participants = array();
        	$soap_wsdl_client = $this->get_soap_wsdl_client( PAUL_URL );
			$params =  array( 'secret_key' => PAUL_SOAP_PASSWORD, 'course_id' => $course_id );
			$result = $soap_wsdl_client->ws_participants( $params );

			// service error
			if ($result instanceof Soap_Fault) {
				throw new Exception("Soap Error - CODE: ". $result->getFault()->faultcode . " String: " . $result->getFault()->faultstring, E_SOAP_SERVICE_ERROR);
			}

			if ( !is_array($result) )
			{
       			if ( $result == -300001 )
       			throw new Exception( "Invalid course_id", E_SOAP_INVALID_INPUT );

       			else if ( $result == -9 )
       			throw new Exception( "Invalid Password", E_SOAP_INVALID_PASSWORD );

       			else if ( $result == -300002 || $result == -300003 ) return array();
			  	// -300002 bedeutet: veranstaltung ohne Teilnehmer
			  	// -300003 bedeutet: kleingruppen ohne Teilnehmer
			}
			else if ( $result['error_code'] == 0 )
			{
				$result = $result['participant'];
				$this->rec_array_check_2($result, $participants);
				return $participants;
			}

			throw new Exception( "Invalid Response Data", E_SOAP_INVALID_RESPONSE_DATA );
       	}


		private function rec_array_check_2($element, &$finalArray)
		{
			if( is_array($element) )
			{
				foreach ($element as $e) $this->rec_array_check_2($e, $finalArray);
			}
			else if ($element instanceof stdClass)
				$finalArray[$element->matric_id] = array( 'mnr' => $element->matric_id, 'status' => intval( $element->status ) );
		}


       	private function checkValidity( $value, $pattern )
       	{
       		$regex_person_id = "^[0-9]{15}$";
       		$regex_person_no = "^[0-9]{1,8}$";
       		$regex_course_name_german = "^.*[A-Za-z].*$";

       		if ( is_array($value) )
       		{
       			// valid response if
       			// - error_code == 0
       			// - response matches its regular expression
       			// - response is not NULL and doesn't consist of zeros solely
       			if ( $value['error_code'] == 0 && ereg(${'regex_'.$pattern}, $value[$pattern] ) && !ereg( "^0*$", $value[$pattern] ) )
       			{
       				return true;
       			}
       			//response is an array, but something went wrong
       			throw new Exception( "Invalid Response Data", E_SOAP_INVALID_RESPONSE_DATA );
       		}

       		//no array -> $value contains directly soap error_code
       		if ( $value == -1 )
       		throw new Exception( "Invalid $pattern", E_SOAP_INVALID_INPUT );

       		else if ( $value == -9 )
       		throw new Exception( "Invalid Password", E_SOAP_INVALID_PASSWORD );

       		return false;
       	}
}

?>
