<?php

class hislsf_soap
{

        private function get_soap_client( $pServiceURL, $pLogin, $pPassword )
        {
                if ( ! $soap_client = new SoapClient( 
                                        $pServiceURL, 
                                        array( "login" => $pLogin, "password" => $pPassword, "trace" => 1, "exceptions" => 1 ) ) )
                {
                        throw new Exception( "Could not connect to HISLSF dbinterface service.", E_CONNECTION );
                }
                return $soap_client;
        }

        public function get_available_courses( $pSemesterID, $pCourseID )
        {
                $soap_client = $this->get_soap_client( HISLSF_DBINTERFACE_SERVICE, HISLSF_SOAP_LOGIN, HISLAF_SOAP_PW );
                $query = 
                        "<SOAPDataService>".
                        "       <general>" .
                        "        <object>Abfrage1</object>" .
                        "       </general>" .
                        "       <veranstaltung.veranstnr>".$pCourseID."</veranstaltung.veranstnr>" .
                        "       <veranstaltung.semester>".$pSemesterID."</veranstaltung.semester>" .
                        "       <user-auth>" .
                        "        <username>" . HISLSF_SOAP_LOGIN . "</username>" .
                        "        <password>" . HISLSF_SOAP_PW. "</password>" .
                        "       </user-auth>" .
                        "</SOAPDataService>";

                $result = $soap_client->getDataXML( $query );

                $result_xml = new SimpleXmlElement( $result );
                return $result_xml;
        }

        public function get_course_information( $pSemesterID, $pLSFObjectID )
        {
                $soap_client = $this->get_soap_client( HISLSF_DBINTERFACE_SERVICE, HISLSF_SOAP_LOGIN, HISLAF_SOAP_PW );
                $query =
                        "<SOAPDataService>".
                        "       <general>" .
                        "        <object>Abfrage2</object>" .
                        "       </general>" .
                        "       <veranstaltung.veranstid>".$pLSFObjectID."</veranstaltung.veranstid>" .
                        "       <veranstaltung.semester>".$pSemesterID."</veranstaltung.semester>" .
                        "       <user-auth>" .
                        "        <username>" . HISLSF_SOAP_LOGIN . "</username>" .
                        "        <password>" . HISLSF_SOAP_PW. "</password>" .
                        "       </user-auth>" .
                        "</SOAPDataService>";

                $result = $soap_client->getDataXML( $query );
                $result_xml = new SimpleXmlElement( $result );

                if ( isset( $result_xml->veranstaltung ) )
                {
                        $course = $result_xml->veranstaltung;
                        $course_infos = array(
                                "course_lsf_id" => (string) $course->Veranstaltungsschluessel,
                                "course_id" => (string) $course->Veranstaltungsnummer,
                                "course_type" => (string) $course->Veranstaltungstyp,
                                "course_name" => (string) $course->Veranstaltungsname,
                                "course_dsc"  => (string) $course->BemerkungZurVeranstaltung,
                                "course_tutors" => "kein Dozent gesetzt"
                        );
                        return $course_infos;
                }
                else
                {
                        return FALSE;
                }
        }

        public function get_participant_list( $pSemesterID, $pLSFObjectID )
        {
                $soap_client = $this->get_soap_client( HISLSF_DBINTERFACE_SERVICE, HISLSF_SOAP_LOGIN, HISLAF_SOAP_PW );
                $query =
                        "<SOAPDataService>".
                        "       <general>" .
                        "        <object>Belegung</object>" .
                        "       </general>" .
                        "       <veranstaltung.veranstid>".$pLSFObjectID."</veranstaltung.veranstid>" .
                        "       <veranstaltung.semester>".$pSemesterID."</veranstaltung.semester>" .
                        "       <user-auth>" .
                        "        <username>" . HISLSF_SOAP_LOGIN . "</username>" .
                        "        <password>" . HISLSF_SOAP_PW. "</password>" .
                        "       </user-auth>" .
                        "</SOAPDataService>";

                $result = $soap_client->getDataXML( $query );
                $result_xml = new SimpleXmlElement( $result );
                return $result_xml;

        }


        public function get_all_bookings( $pSemesterID )
        {
                $soap_client = $this->get_soap_client( HISLSF_DBINTERFACE_SERVICE, HISLSF_SOAP_LOGIN, HISLAF_SOAP_PW );
                $query =
                        "<SOAPDataService>".
                        "       <general>" .
                        "        <object>Belegung1</object>" .
                        "       </general>" .
                        "       <condition>".
                        "       <semester>".$pSemesterID."</semester>" .
                        "       </condition>".
                        "       <user-auth>" .
                        "        <username>" . HISLSF_SOAP_LOGIN . "</username>" .
                        "        <password>" . HISLSF_SOAP_PW. "</password>" .
                        "       </user-auth>" .
                        "</SOAPDataService>";

                $result = $soap_client->getDataXML( $query );
                $result_xml = new SimpleXmlElement( $result );
                return $result_xml;
        }


}

?>