<?php
class lms_ldap
{

        public $ldap_conn;

        public function __construct()
        {
                if ( ! $this->ldap_conn = ldap_connect( LDAP_SERVER, LDAP_PORT ) )
                {
                        throw new Exception( "No connection to " . LDAP_SERVER . " on port " . LDAP_PORT, E_CONNECTION );
                }

                if ( ! ldap_set_option( $this->ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3 ) )
                {
                        throw new Exception( "LDAP protocol version 3 not supported", E_CONF );
                }
                if ( ! ldap_start_tls( $this->ldap_conn ) )
                {
                        throw new Exception( "LDAP TLS not started", E_CONNECTION );
                }

        }

        public function bind( $login, $password )
        {
                return ldap_bind( $this->ldap_conn, $login, $password );
        }

        public function get_ldap_attribute( $pAttributes, $pUid )
        {
                if ( ! is_array( $pAttributes ) )
                {
                        throw new Exception( "Argument is not an array", E_PARAM );
                }
				
                $search = ldap_search( $this->ldap_conn, LDAP_O . ", " . LDAP_C, "uid=" . $pUid, $pAttributes );
                $entry  = ldap_first_entry( $this->ldap_conn, $search );

                $result = array();
                foreach( $pAttributes as $attribute )
                {
                        $value = ldap_get_values( $this->ldap_conn, $entry, $attribute );
                        $result[ $attribute ] = $value[ 0 ];
                }
                return $result;
        }
        
        public function get_ldap_attribute_for_various_data( $pAttributes, $ldapRequest )
        {
                $allResults = array();
        		$ldapc = $this->ldap_conn; //ldap connection
                
                if ( ! is_array( $pAttributes ) )
                {
                        throw new Exception( "Argument is not an array", E_PARAM );
                }
				
                //search for multiple ldap attributes
                //search string is prefix notation
                $search = ldap_search( $ldapc, LDAP_O . ", " . LDAP_C, $ldapRequest, $pAttributes );
                
                //get ldap attributes
                for ($entryID=ldap_first_entry($ldapc,$search); $entryID!=false; $entryID=ldap_next_entry($ldapc,$entryID)){
					foreach( $pAttributes as $attribute )
	                {
	                	$value = ldap_get_values( $ldapc, $entryID, $attribute );
	                	$result[ $attribute ] = $value[ 0 ];
	                }
	                $allResults[]=$result;
				}
				return $allResults;
        }
        

        public function studentid2uid( $pStudentId )
        {
                if ( empty( $pStudentId ) )
                {
                        throw new Exception( "No parameter given", E_PARAM );
                }

                $dn     = LDAP_OU . ", " . LDAP_O . ", " . LDAP_C;
                $filter = "(&(objectclass=" . LDAP_OBJECTCLASS_STUDENT . ")(" . LDAP_ATTRIBUTE_STUDID . "=" . $pStudentId . "))"; 

                $search = ldap_search( $this->ldap_conn, $dn, $filter, array( "uid" ) );
                $entry = ldap_first_entry( $this->ldap_conn, $search );

                $result = @ldap_get_values( $this->ldap_conn, $entry, "uid" );
                ldap_free_result( $search );
                return $result[ 0 ];
        }

        public function __destruct()
        {
                ldap_close( $this->ldap_conn );
        }

}

?>
