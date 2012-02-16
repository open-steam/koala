<?php
namespace Profile\Commands;
class Privacy extends \AbstractCommand implements \IFrameCommand {

	private $params;
	private $id;

	public function validateData(\IRequestObject $requestObject) {
		return true;
	}

	public function processData(\IRequestObject $requestObject) {
		$this->params = $requestObject->getParams();
		isset($this->params[0]) ? $this->id = $this->params[0]: "";
	}
	public function frameResponse(\FrameResponseObject $frameResponseObject) {
		//$frameResponseObject->setTitle("Edit");
		$frameResponseObject = $this->execute($frameResponseObject);
		return $frameResponseObject;
	}
	public function set_checkbox( $name, $integer_value, &$content )
	{	
		$checked="";
		$allusers_checked="";
		$nobody="";
		if($integer_value < PROFILE_DENY_ALLUSERS){
			$allusers_checked = "checked=\"checked\"";
		}else if( $integer_value < PROFILE_DENY_CONTACTS){
			$checked="checked=\"checked\"";		
		}else{
			$nobody = "checked=\"checked\"";	
		}
		$content->setVariable( $name . "_ALLUSERS_CHECKED", $allusers_checked );
		$content->setVariable( $name . "_CONTACTS_CHECKED", $checked );
		$content->setVariable( $name . "_NONE_CHECKED", $nobody );
		//if( constant("ENABLED_" . $name) || constant("ENABLED_BID_". $name) ){

		//}

		/*
		 $checked = ( $integer_value & PROFILE_DENY_COURSEMATES ) ? "" : "checked=\"checked\"";
		 $content->setVariable( $name . "_COURSEMATES_CHECKED", $checked );
		 $content->setVariable( $name . "_COURSEMATES_DISABLED", $disabled );

		 $checked = ( $integer_value & PROFILE_DENY_GROUPMATES ) ? "" : "checked=\"checked\"";
		 $content->setVariable( $name . "_GROUPMATES_CHECKED", $checked );
		 $content->setVariable( $name . "_GROUPMATES_DISABLED", $disabled );
		 */
	}
	public function state_to_binary( $states )
	{
		$deny_all = PROFILE_DENY_ALLUSERS + PROFILE_DENY_CONTACTS; // + PROFILE_DENY_COURSEMATES + PROFILE_DENY_GROUPMATES;

		if ( $states == null ) return $deny_all;
		if ( in_array( "allusers", $states ) ) return 0;

		$binary = PROFILE_DENY_ALLUSERS;
		$binary += ( in_array("contacts", $states) ) ? 0 : PROFILE_DENY_CONTACTS;
		// $binary += ( in_array("coursemates", $states) ) ? 0 : PROFILE_DENY_COURSEMATES;
		// $binary += ( in_array("groupmates", $states) ) ? 0 : PROFILE_DENY_GROUPMATES;

		return $binary;
	}

	public function execute (\FrameResponseObject $frameResponseObject) {
		//$portal = \lms_portal::get_instance();
		//$portal->initialize( GUEST_NOT_ALLOWED );
		//$portal->set_page_title( gettext( "Profile Privacy" ) );
		$user = \lms_steam::get_current_user();

		$cache = get_cache_function( $user->get_name(), 86400 );
		$user_privacy = $cache->call( "\lms_steam::user_get_profile_privacy", $user->get_name(), TRUE );

		if ( $_SERVER[ "REQUEST_METHOD" ] == "POST")
		{
			$binary_values = array();
			$binary_values["PRIVACY_STATUS"] =				$this->state_to_binary( $_POST["status"] );
			$binary_values["PRIVACY_GENDER"] =				$this->state_to_binary( $_POST["gender"] );
			$binary_values["PRIVACY_FACULTY"] =				$this->state_to_binary( $_POST["faculty"] );
			$binary_values["PRIVACY_MAIN_FOCUS"] =			$this->state_to_binary( $_POST["main_focus"] );
			$binary_values["PRIVACY_WANTS"] =				$this->state_to_binary( $_POST["wants"] );
			$binary_values["PRIVACY_HAVES"] =				$this->state_to_binary( $_POST["haves"] );
			$binary_values["PRIVACY_ORGANIZATIONS"] =		$this->state_to_binary( $_POST["organizations"] );
			$binary_values["PRIVACY_HOMETOWN"] =			$this->state_to_binary( $_POST["hometown"] );
			$binary_values["PRIVACY_OTHER_INTERESTS"] =		$this->state_to_binary( $_POST["other_interests"] );
			$binary_values["PRIVACY_LANGUAGES"] =			$this->state_to_binary( $_POST["languages"] );
			$binary_values["PRIVACY_CONTACTS"] =			$this->state_to_binary( $_POST["contacts"] );
			$binary_values["PRIVACY_GROUPS"] =				$this->state_to_binary( $_POST["groups"] );
			$binary_values["PRIVACY_EMAIL"] =				$this->state_to_binary( $_POST["email"] );
			$binary_values["PRIVACY_ADDRESS"] =				$this->state_to_binary( $_POST["address"] );
			$binary_values["PRIVACY_TELEPHONE"] =			$this->state_to_binary( $_POST["telephone"] );
			$binary_values["PRIVACY_PHONE_MOBILE"] =		$this->state_to_binary( $_POST["phone_mobile"] );
			$binary_values["PRIVACY_WEBSITE"] =				$this->state_to_binary( $_POST["website"] );
			$binary_values["PRIVACY_ICQ_NUMBER"] =			$this->state_to_binary( $_POST["icq_number"] );
			$binary_values["PRIVACY_MSN_IDENTIFICATION"] =	$this->state_to_binary( $_POST["msn_identification"] );
			$binary_values["PRIVACY_AIM_ALIAS"] =			$this->state_to_binary( $_POST["aim_alias"] );
			$binary_values["PRIVACY_YAHOO_ID"] =			$this->state_to_binary( $_POST["yahoo_id"] );
			$binary_values["PRIVACY_SKYPE_NAME"] =			$this->state_to_binary( $_POST["skype_name"] );

			$privacy_object = $user->get_attribute( "KOALA_PRIVACY" );

			if ( !( $privacy_object instanceof \steam_object ) )
			{
				$privacy_object = \steam_factory::create_object( $GLOBALS[ "STEAM" ]->get_id(), "privacy profile", CLASS_OBJECT );

				if ( !( $privacy_object instanceof \steam_object ) )
				throw new \Exception("Error creating Privacy-Proxy-Object", E_USER_NO_PRIVACYPROFILE);

				$user->set_attribute( "KOALA_PRIVACY", $privacy_object );
				$privacy_object->set_acquire( $user );
			}

			$privacy_object->set_attributes( $binary_values );

			/*
			 require_once( "Cache/Lite.php" );
			 $cache = new Cache_Lite( array( "cacheDir" => PATH_CACHE ) );
			 $cache->clean( $user->get_name() );
			 $cache->clean( $user->get_id() );
			 */

			$cache = get_cache_function( \lms_steam::get_current_user()->get_name() );
			$cache->drop( "\lms_portal::get_menu_html", \lms_steam::get_current_user()->get_name(), TRUE );

			$cache = get_cache_function( $user->get_name() );
			$cache->drop( "\lms_steam::user_get_profile_privacy", $user->get_name(), TRUE );

			$_SESSION[ "confirmation" ] = gettext( "Your profile data has been saved." );
			header( "Location: " . PATH_URL . "profile/privacy/".$user->get_name() );

		}
		$content = \Profile::getInstance()->loadTemplate("profile_privacy.template.html");
		//$content = new HTML_TEMPLATE_IT();
		//$content->loadTemplateFile( PATH_TEMPLATES . "profile_privacy.template.html" );
		if(ENABLED_CONTACTS_GROUPS_TITLE)
		$content->setVariable( "HEADER_CONTACTS_AND_GROUPS", gettext( "Contacts and Groups" ) );
		if(ENABLED_CONTACTS_TITLE)
		$content->setVariable( "HEADER_CONTACT_DATA", gettext( "Contact Data" ) );
		$content->setVariable( "INFO_TEXT", gettext( "Here you can set which persons can see what information on your profile page." ) );

		$content->setVariable( "LABEL_ALLUSERS", gettext( "All Users" ) );
		if(PLATFORM_ID == "bid"){
			$content->setVariable( "LABEL_CONTACTS", "Favoriten" );
		}

		if(ENABLED_CONTACTS)
		//$labelContacts = gettext( "Contacts" );
		if(ENABLED_PROFILE_TITLE)
		$content->setVariable( "LABEL_CONTACTS", gettext( "Contacts" ) );
		//$content->setVariable( "LABEL_COURSEMATES", gettext( "Course Mates" ) );
		//$content->setVariable( "LABEL_GROUPMATES", gettext( "Group Mates" ) );

		if(ENABLED_STATUS)
		//$labelStatus = gettext("Status");
		$content->setVariable( "LABEL_STATUS", gettext( "Status" ) );
		if(ENABLED_BID_DESCIPTION){
			$content->setVariable( "LABEL_STATUS", "Beschreibung" );
		}
		if(ENABLED_GENDER)
		//$labelGender = gettext( "Gender" );
		$content->setVariable( "LABEL_GENDER", gettext( "Gender" ) );
		if(ENABLED_FACULTY)
		//$labelFaculty = gettext( "Origin" );
		$content->setVariable( "LABEL_FACULTY", gettext( "Origin" ) );
		if(ENABLED_MAIN_FOCUS)
		//$labelMainFocus = gettext( "Main focus" );
		$content->setVariable( "LABEL_MAIN_FOCUS", gettext( "Main focus" ) );
		if(ENABLED_WANTS)
		//$labelWants = gettext( "Wants" );
		$content->setVariable( "LABEL_WANTS", gettext( "Wants" ) );
		if(ENABLED_HAVES)
		//$labelHaves = gettext("Haves");
		$content->setVariable( "LABEL_HAVES", gettext( "Haves" ) );
		if(ENABLED_ORGANIZATIONS)
		//$labelOrganizations = gettext( "Organizations" );
		$content->setVariable( "LABEL_ORGANIZATIONS", gettext( "Organizations" ) );
		if(ENABLED_HOMETOWN)
		//$labelHometown = gettext( "Hometown" );
		$content->setVariable( "LABEL_HOMETOWN", gettext( "Hometown" ) );
		if(ENABLED_OTHER_INTERESTS)
		//$labelOtherInterests = gettext( "Other interests" );
		$content->setVariable( "LABEL_OTHER_INTERESTS", gettext( "Other interests" ) );
		if(ENABLED_LANGUAGES)
		//$labelLanguages = gettext( "Language" );
		$content->setVariable( "LABEL_LANGUAGES", gettext( "Language" ) );
		//$content->setVariable( "LABEL_CONTACTS", gettext( "Contacts" ) ); -> siehe oben
		if(ENABLED_GROUPS)
		//$labelGroups = gettext( "Groups" );
		$content->setVariable( "LABEL_GROUPS", gettext( "Groups" ) );
		if(ENABLED_EMAIL || ENABLED_BID_EMAIL)
		//$labelMail = gettext( "E-mail" );
		$content->setVariable( "LABEL_EMAIL", gettext( "E-mail" ) );
		if(ENABLED_ADDRESS || ENABLED_BID_ADRESS)
		//$labelAdress = gettext( "Address" );
		$content->setVariable( "LABEL_ADDRESS", gettext( "Address" ) );
		if(ENABLED_TELEPHONE || ENABLED_BID_PHONE)
		//$labelTelephone = gettext( "Phone" );
		$content->setVariable( "LABEL_TELEPHONE", gettext( "Phone" ) );
		if(ENABLED_PHONE_MOBILE)
		//$labelPhoneMobile = gettext( "Phone, mobile" );
		$content->setVariable( "LABEL_PHONE_MOBILE", gettext( "Phone, mobile" ) );
		if(ENABLED_WEBSITE)
		//$labelWebsite = gettext( "Website" );
		$content->setVariable( "LABEL_WEBSITE", gettext( "Website" ) );
		if(ENABLED_ICQ_NUMBER || ENABLED_BID_IM)
		//$labelIcqNumber = gettext( "ICQ number" );
		$content->setVariable( "LABEL_ICQ_NUMBER", gettext( "ICQ number" ) );
		if(ENABLED_MSN_IDENTIFICATION || ENABLED_BID_IM)
		//$labelMsnIdentification = gettext( "MSN identification" );
		$content->setVariable( "LABEL_MSN_IDENTIFICATION", gettext( "MSN identification" ) );
		if(ENABLED_AIM_ALIAS || ENABLED_BID_IM)
		//$labelAimAlias = gettext( "AIM-alias" );
		$content->setVariable( "LABEL_AIM_ALIAS", gettext( "AIM-alias" ) );
		if(ENABLED_YAHOO_ID || ENABLED_BID_IM)
		//$labelYahooId = gettext( "Yahoo-ID" );
		$content->setVariable( "LABEL_YAHOO_ID", gettext( "Yahoo-ID" ) );
		if(ENABLED_SKYPE_NAME || ENABLED_BID_IM)
		//$labelSkypeName = gettext( "Skype name" );
		$content->setVariable( "LABEL_SKYPE_NAME", gettext( "Skype name" ) );

		$content->setVariable( "LABEL_SAVE_IT", gettext( "Save changes" )  );

		//TODO: Bei Bedarf wieder einbauen!
		//$content->setVariable( "BACK_LINK", "<a href=\"" . PATH_URL . "profile/index/" . $user->get_name() . "/\">" . gettext( "back to your user profile" ) . "</a>" );

		$deny_all = PROFILE_DENY_ALLUSERS + PROFILE_DENY_CONTACTS;
		if(ENABLED_STATUS || ENABLED_BID_DESCIPTION)
		(isset($user_privacy[ "PRIVACY_STATUS" ])) ? $this->set_checkbox("STATUS", $user_privacy[ "PRIVACY_STATUS" ], $content): $this->set_checkbox("STATUS",$deny_all, $content);
		if(ENABLED_GENDER)
		(isset($user_privacy[ "PRIVACY_GENDER" ])) ? $this->set_checkbox("GENDER", $user_privacy[ "PRIVACY_GENDER" ], $content):$this->set_checkbox("GENDER",$deny_all, $content);
		if(ENABLED_FACULTY)
		(isset($user_privacy[ "PRIVACY_FACULTY" ])) ? $this->set_checkbox("FACULTY", $user_privacy[ "PRIVACY_FACULTY" ], $content):$this->set_checkbox("FACULTY",$deny_all, $content);
		if(ENABLED_MAIN_FOCUS)
		(isset($user_privacy[ "PRIVACY_MAIN_FOCUS" ])) ? $this->set_checkbox("MAIN_FOCUS", $user_privacy[ "PRIVACY_MAIN_FOCUS" ], $content):$this->set_checkbox("MAIN_FOCUS",$deny_all, $content);
		if(ENABLED_WANTS)
		(isset($user_privacy[ "PRIVACY_WANTS" ])) ? $this->set_checkbox("WANTS", $user_privacy[ "PRIVACY_WANTS" ], $content):$this->set_checkbox("WANTS",$deny_all, $content);
		if(ENABLED_HAVES)
		(isset($user_privacy[ "PRIVACY_HAVES" ])) ? $this->set_checkbox("HAVES", $user_privacy[ "PRIVACY_HAVES" ], $content):$this->set_checkbox("HAVES",$deny_all, $content);
		if(ENABLED_ORGANIZATIONS)
		(isset($user_privacy[ "PRIVACY_ORGANIZATIONS" ])) ? $this->set_checkbox("ORGANIZATIONS", $user_privacy[ "PRIVACY_ORGANIZATIONS" ], $content):$this->set_checkbox("ORGANIZATIONS",$deny_all, $content);
		if(ENABLED_HOMETOWN)
		(isset($user_privacy[ "PRIVACY_HOMETOWN" ])) ? $this->set_checkbox("HOMETOWN", $user_privacy[ "PRIVACY_HOMETOWN" ], $content):$this->set_checkbox("HOMETOWN",$deny_all, $content);
		if(ENABLED_OTHER_INTERESTS)
		(isset($user_privacy[ "PRIVACY_OTHER_INTERESTS" ])) ? $this->set_checkbox("OTHER_INTERESTS", $user_privacy[ "PRIVACY_OTHER_INTERESTS" ], $content):$this->set_checkbox("OTHER_INTERESTS",$deny_all, $content);
		if(ENABLED_LANGUAGES)
		(isset($user_privacy[ "PRIVACY_LANGUAGES" ])) ? $this->set_checkbox("LANGUAGES", $user_privacy[ "PRIVACY_LANGUAGES" ], $content):$this->set_checkbox("LANGUAGES",$deny_all, $content);
		if(ENABLED_CONTACTS)
		(isset($user_privacy[ "PRIVACY_CONTACTS" ])) ? $this->set_checkbox("CONTACTS", $user_privacy[ "PRIVACY_CONTACTS" ], $content):$this->set_checkbox("CONTACTS",$deny_all, $content);
		if(ENABLED_GROUPS)
		(isset($user_privacy[ "PRIVACY_GROUPS" ])) ? $this->set_checkbox("GROUPS", $user_privacy[ "PRIVACY_GROUPS" ], $content):$this->set_checkbox("GROUPS",$deny_all, $content);
		if(ENABLED_EMAIL || ENABLED_BID_EMAIL)
		(isset($user_privacy[ "PRIVACY_EMAIL" ])) ? $this->set_checkbox("EMAIL", $user_privacy[ "PRIVACY_EMAIL" ], $content):$this->set_checkbox("EMAIL",$deny_all, $content);
		if(ENABLED_ADDRESS || ENABLED_BID_ADRESS)
		(isset($user_privacy[ "PRIVACY_ADDRESS" ])) ? $this->set_checkbox("ADDRESS", $user_privacy[ "PRIVACY_ADDRESS" ], $content):$this->set_checkbox("ADDRESS",$deny_all, $content);
		if(ENABLED_TELEPHONE || ENABLED_BID_PHONE)
		(isset($user_privacy[ "PRIVACY_TELEPHONE" ])) ? $this->set_checkbox("TELEPHONE", $user_privacy[ "PRIVACY_TELEPHONE" ], $content):$this->set_checkbox("TELEPHONE",$deny_all, $content);
		if(ENABLED_PHONE_MOBILE)
		(isset($user_privacy[ "PRIVACY_PHONE_MOBILE" ])) ? $this->set_checkbox("PHONE_MOBILE", $user_privacy[ "PRIVACY_PHONE_MOBILE" ], $content):$this->set_checkbox("PHONE_MOBILE",$deny_all, $content);
		if(ENABLED_WEBSITE)
		(isset($user_privacy[ "PRIVACY_WEBSITE" ])) ? $this->set_checkbox("WEBSITE", $user_privacy[ "PRIVACY_WEBSITE" ], $content):$this->set_checkbox("WEBSITE",$deny_all, $content);
		if(ENABLED_ICQ_NUMBER || ENABLED_BID_IM)
		(isset($user_privacy[ "PRIVACY_ICQ_NUMBER" ])) ? $this->set_checkbox("ICQ_NUMBER", $user_privacy[ "PRIVACY_ICQ_NUMBER" ], $content):$this->set_checkbox("ICQ_NUMBER",$deny_all, $content);
		if(ENABLED_MSN_IDENTIFICATION || ENABLED_BID_IM)
		(isset($user_privacy[ "PRIVACY_MSN_IDENTIFICATION" ])) ? $this->set_checkbox("MSN_IDENTIFICATION", $user_privacy[ "PRIVACY_MSN_IDENTIFICATION" ], $content):$this->set_checkbox("MSN_IDENTIFICATION",$deny_all, $content);
		if(ENABLED_AIM_ALIAS || ENABLED_BID_IM)
		(isset($user_privacy[ "PRIVACY_AIM_ALIAS" ])) ? $this->set_checkbox("AIM_ALIAS", $user_privacy[ "PRIVACY_AIM_ALIAS" ], $content):$this->set_checkbox("AIM_ALIAS",$deny_all, $content);
		if(ENABLED_YAHOO_ID || ENABLED_BID_IM )
		(isset($user_privacy[ "PRIVACY_YAHOO_ID" ])) ? $this->set_checkbox("YAHOO_ID", $user_privacy[ "PRIVACY_YAHOO_ID" ], $content):$this->set_checkbox("YAHOO_ID",$deny_all, $content);
		if(ENABLED_SKYPE_NAME || ENABLED_BID_IM)
		(isset($user_privacy[ "PRIVACY_SKYPE_NAME" ])) ? $this->set_checkbox("SKYPE_NAME", $user_privacy[ "PRIVACY_SKYPE_NAME" ], $content):$this->set_checkbox("SKYPE_NAME",$deny_all, $content);
		if(PLATFORM_ID=="bid"){
			$frameResponseObject->setHeadline(array(
			array( "link" => PATH_URL . "home/",
			"name" => $user->get_attribute( "USER_FIRSTNAME" ) . " " . $user->get_attribute( "USER_FULLNAME" )
			),
			array( "link" => PATH_URL . "profile/index/" . $user->get_name() . "/",
			"name" => gettext( "Profile" )
			),
			array( "link" => "",
			"name" => gettext( "Privacy" )
			)
			));
		}
		else{
			$frameResponseObject->setHeadline(array(
			array( "link" => PATH_URL . "profile/index/" . $user->get_name() . "/",
			"name" => $user->get_attribute( "USER_FIRSTNAME" ) . " " . $user->get_attribute( "USER_FULLNAME" )
			),
			array( "link" => PATH_URL . "profile/index/" . $user->get_name() . "/",
			"name" => gettext( "Profile" )
			),
			array( "link" => "",
			"name" => gettext( "Privacy" )
			)
			));
		}

		$rawHtml = new \Widgets\RawHtml();
		$rawHtml->setHtml($content->get());
		$frameResponseObject->addWidget($rawHtml);
		return $frameResponseObject;



	}
}






?>