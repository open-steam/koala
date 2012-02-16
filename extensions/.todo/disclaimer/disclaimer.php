<?php

	require_once( "../etc/koala.conf.php" );

	$portal = lms_portal::get_instance();
	$portal->initialize( GUEST_ALLOWED );
	if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" && $_POST[ "uid"] == $_SESSION[ "LDAP_LOGIN" ] )
	{
		// ERSTMAL NOCH EINMAL LDAP UEBERPRUEFEN
		$ldap_client = new lms_ldap();
		$dn = "uid=" . $_SESSION[ "LDAP_LOGIN" ] . ", " . LDAP_OU . ", " . LDAP_O . ", " . LDAP_C;
		if ( ! $ldap_client->bind( $dn , decrypt( $_SESSION[ "LDAP_PASSWORD_ENCR" ], ENCRYPTION_KEY ) ) )
		{
			// TODO
			throw new Exception( );
		}

		$client = new SoapClient(NULL, array(
			"location"      => LDAP_SERVICE_LOCATION,
			"uri"           => LDAP_SERVICE_URL,
			"local_cert"    => LDAP_SERVICE_CERT
		));
		$parameter = array( new SoapParam( $_SESSION[ "LDAP_LOGIN" ], "uid" ) );
		try
		{
			$result = $client->__soapCall( "getState", $parameter );
		}
		catch( Exception $e )
		{
			$portal->set_problem_description( "SOAP SERVICE NICHT ERREICHBAR" );
		}

		// SERVICE FREISCHALTEN
		// UND EINLOGGE

		// 2 FAELLE: ENTWEDER SERVICE IST BEANTRAGT => FREISCHALTEN
		// SERVICE IST NICHT BEANTRAGT, DANN BEANTRAGEN UND DANN FREISCHALTEN

		echo "Antragsstatus: " . $result . "\n";

		if( ( ! strcmp( $result, "NONE" ) ) || ( ! strcmp( $result, "WANTED" ) ) ) {

			if( ! strcmp( $result, "NONE" ) ) {
				// Antrag stellen
				$result = $client->__soapCall( 'request', $parameter );
				echo "Antragsstatus: " . $result . "\n";
			}

			// Antragstatus bestimmen
			$result = $client->__soapCall( 'getState', $parameter );
			echo "Antragsstatus: " . $result . "\n";

			// Antrag genehmigen
			$result = $client->__soapCall( 'approve', $parameter );
			echo "Antragsstatus: " . $result . "\n";

			// Antragstatus bestimmen
			$result = $client->__soapCall( 'getState', $parameter );
			echo "Antragsstatus: " . $result . "\n";
		}
	}

elseif( ! empty( $_SESSION[ "LDAP_LOGIN" ] ) && ! empty( $_SESSION[ "LDAP_PASSWORD_ENCR" ] ) )
{

	$content = new HTML_TEMPLATE_IT( PATH_TEMPLATES );
	$content->loadTemplateFile( "disclaimer.de_DE.html" );


	$content->setVariable( "PATH_IMAGES", PATH_STYLE . "images/" );

	$content->setVariable( "LDAP_UID", $_SESSION[ "LDAP_LOGIN" ]);
	$portal->set_page_title( gettext( "Disclaimer" ) );
	$portal->set_page_main( gettext( "Disclaimer" ), $content->get() );
}
else
{
	print( "FALSCH AUFGERUFEN" );
}
$portal->show_html();

?>
