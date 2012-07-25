<?php
namespace Profile\Commands;
class Edit extends \AbstractCommand implements \IFrameCommand {

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

	public function safe_string ( $text, $default_result = "" ) {
		return is_string($text) ? $text : $default_result;
	}
	public function execute (\FrameResponseObject $frameResponseObject) {
		$user = \lms_steam::get_current_user();
		$cache = get_cache_function( $user->get_name(), 86400 );
		$user_profile = $cache->call( "lms_steam::user_get_profile", $user->get_name() );

		if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
		{
			$values = $_POST[ "values" ];
			foreach ($values as $i=>$val){
				$values[$i]=htmlspecialchars($val);
			}


			if ( !empty( $values[ "USER_PROFILE_WEBSITE_URI" ] ) && substr( $values[ "USER_PROFILE_WEBSITE_URI" ], 0, 7 ) != "http://" )
			{
				$values[ "USER_PROFILE_WEBSITE_URI" ] = "http://" . $values[ "USER_PROFILE_WEBSITE_URI" ];
			}

			$user->set_attributes( $values );
			if ( !empty( $values[ "USER_PROFILE_FACULTY" ] )){
				$old_fac_id = $user_profile[ "USER_PROFILE_FACULTY" ];
				$new_fac_id = $values[ "USER_PROFILE_FACULTY" ];

					
				if ( $new_fac_id != $old_fac_id )
				{
					if ( $old_fac_id > 0 )
					{
						$old_faculty = \steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $old_fac_id, CLASS_GROUP );
						$old_faculty->remove_member( $user );
					}
					if ( $new_fac_id > 0 )
					{
						$new_faculty = \steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $new_fac_id, CLASS_GROUP );
						$new_faculty->add_member( $user );
					}
				}

			}

			if(!empty($values["USER_LANGUAGE"]) ){
				$lang_index = \language_support::get_language_index();
				\language_support::choose_language( $lang_index[ $values["USER_LANGUAGE"] ] );
					
			}


			$cache = get_cache_function( \lms_steam::get_current_user()->get_name() );
			$cache->drop( "lms_portal::get_menu_html", \lms_steam::get_current_user()->get_name(), TRUE );

			$cache = get_cache_function( $user->get_name() );
			$cache->drop( "lms_steam::user_get_profile", $user->get_name() );

			$_SESSION[ "confirmation" ] = gettext( "Your profile data has been saved." );

			header( "Location: " . PATH_URL . "profile/edit" );
		}
		$content = \Profile::getInstance()->loadTemplate("edit.template.html");
		if(PLATFORM_ID=="bid"){
			$content->setVariable( "LABEL_INFO", "Hier können Sie Ihre persönlichen Kontaktdaten einrichten. Bis auf Ihren Namen sind alle Angaben freiwillig und können von Ihnen geändert werden. Klicken Sie auf den Button <b><i>Profil-Privatsphäre</i></b> um festzulegen, wem welche Informationen angezeigt werden sollen.");
		}else{
			$content->setVariable( "LABEL_INFO", gettext( "Please complete your profile. None of the fields are mandatory. Some of the fields can not be changed due to central identity management at the IMT.<br/><b>Note: With the button <i>Profile Privacy</i> you can control which information can be seen by other users.</b>" ) );
		}
		if(ENABLED_PROFILE_TITLE)
		$content->setVariable( "LABEL_PROFILE", gettext( "General Information" ) );
		$content->setVariable( "LABEL_LOOKING", gettext( "Your buddy icon" ) );
		$content->setVariable( "LABEL_MAIL_PREFS", gettext( "Your mail preferences" ) );
		$content->setVariable( "LABEL_PROFILE_PRIVACY", gettext( "Profile Privacy" ) );
		$content->setVariable( "LINK_BUDDY_ICON", PATH_URL . "profile/image" );
		$content->setVariable( "LINK_MAIL_PREFS", PATH_URL . "messages_prefs.php" );
		$content->setVariable( "LINK_PROFILE_PRIVACY", PATH_URL . "profile/privacy" );
		if(ENABLED_FIRST_NAME)
		$content->setVariable( "LABEL_FIRST_NAME", gettext( "First name" ) );
		if(ENABLED_FULL_NAME)
		$content->setVariable( "LABEL_LAST_NAME", gettext( "Last name" ) );
		if(ENABLED_DEGREE){
			$content->setVariable( "LABEL_TITLE", gettext( "Academic title" ) );
			$content->setVariable( "LABEL_DEGREE", gettext( "Academic degree" ) );
			$content->setVariable( "LABEL_IF_AVAILABLE", gettext( "only if available" ) );
			$academicTitle = (String) $user_profile[ "USER_ACADEMIC_TITLE" ];
			switch( $academicTitle )
			{
				case "Dr.":
					$content->setVariable( "TITLE_DR_SELECTED", 'selected="selected"'  );
					break;
				case ( "PD Dr." ):
					$content->setVariable( "TITLE_PRIVDOZDR_SELECTED", 'selected="selected"'  );
					break;
				case ( "Prof." ):
					$content->setVariable( "TITLE_PROF_SELECTED", 'selected="selected"'  );
					break;
				case ( "Prof. Dr." ):
					$content->setVariable( "TITLE_PROFDR_SELECTED", 'selected="selected"'  );
					break;
				default:
					$content->setVariable( "TITLE_NULL_SELECTED", 'selected="selected"' );
					break;
			}
			$content->setVariable( "VALUE_ACADEMIC_DEGREE", $this->safe_string( $user_profile[ "USER_ACADEMIC_DEGREE" ] ) );


		}
		if(ENABLED_BID_DESCIPTION){
			$content->setVariable( "LABEL_STATUS_BID", gettext("Description") );
		}
		if(ENABLED_STATUS){
			$content->setVariable( "LABEL_STATUS", gettext( "Status" ) );
		}
		if(ENABLED_GENDER){
			$content->setVariable( "LABEL_GENDER", gettext( "Gender" ) );
			$content->setVariable( "LABEL_FEMALE", gettext( "female" ) );
			$content->setVariable( "LABEL_MALE", gettext( "male" ) );
			$content->setVariable( "LABEL_NOT_SAY", gettext( "rather not say" ) );
		}
		if(ENABLED_FACULTY){
			$content->setVariable( "LABEL_FACULTY", gettext( "Origin" ) );
		}
		if(ENABLED_MAIN_FOCUS){
			$content->setVariable( "LABEL_MAIN_FOCUS", gettext( "Main focus" ) );
		}
		if(ENABLED_HOMETOWN){
			$content->setVariable( "LABEL_HOMETOWN", gettext( "Hometown" ) );
		}
		if(ENABLED_WANTS)
		$content->setVariable( "LABEL_WANTS", gettext( "Wants" ) );
		if(ENABLED_HAVES)
		$content->setVariable( "LABEL_HAVES", gettext( "Haves" ) );
		if(ENABLED_OTHER_INTERESTS)
		$content->setVariable( "LABEL_OTHER_INTERESTS", gettext( "Other interests" ) );
		if(ENABLED_ORGANIZATIONS)
		$content->setVariable( "LABEL_ORGANIZATIONS", gettext( "Organizations" ) );
		if(ENABLED_USER_DESC)
		$content->setVariable( "LABEL_DESCRIBE_YOURSELF", gettext( "Describe yourself" ) );
		if(ENABLED_CONTACTS_TITLE)
		$content->setVariable( "LABEL_CONTACT_DATA", gettext( "Contact Data") );
		if(ENABLED_EMAIL){
			$content->setVariable( "LABEL_EMAIL", gettext( "E-mail" ) );
			$content->setVariable( "LABEL_EMAIL_PREFERENCES", gettext( "Looking for your e-mail preferences?" ) );
			//$content->setVariable( "LINK_EMAIL_PREFERENCES", PATH_URL . "messages_prefs.php" );
		}
		if(ENABLED_TELEPHONE ){
			//$content->setVariable( "LABEL_TELEPHONE", gettext( "Phone" ) );
			$content->setVariable( "LABEL_TELEPHONE", "Telefon" );

		}

		$content->setVariable( "LABEL_MOBILE", gettext( "Phone, mobile" ) );
		if(ENABLED_ADDRESS)
		$content->setVariable( "LABEL_ADDRESS", gettext( "Address" ) );
		if(ENABLED_PHONE_MOBILE)
		$content->setVariable( "LABEL_PHONE_MOBILE", gettext( "Phone, mobile" ) );
		if(ENABLED_WEBSITE){
			$content->setVariable( "LABEL_WEBSITE", gettext( "Website" ) );
			$content->setVariable( "LABEL_WEBSITE_NAME", gettext( "Website name" ) );
		}
		//TODO: IM-Speichern dementsprechend anpassen
		if(ENABLED_ICQ_NUMBER || ENABLED_BID_IM)
		$content->setVariable( "LABEL_ICQ_NUMBER", gettext( "ICQ number" ) );
		if(ENABLED_MSN_IDENTIFICATION || ENABLED_BID_IM)
		$content->setVariable( "LABEL_MSN_IDENTIFICATION", gettext( "MSN identification" ) );
		if(ENABLED_AIM_ALIAS || ENABLED_BID_IM)
		$content->setVariable( "LABEL_AIM_ALIAS", gettext( "AIM-alias" ) );
		if(ENABLED_YAHOO_ID || ENABLED_BID_IM)
		$content->setVariable( "LABEL_YAHOO_ID", gettext( "Yahoo-ID" ) );
		if(ENABLED_SKYPE_NAME || ENABLED_BID_IM)
		$content->setVariable( "LABEL_SKYPE_NAME", gettext( "Skype name" ) );

		$content->setVariable( "INFO_INCLUDE_HTTP", gettext( "Please include the 'http://'" ) );

		$content->setVariable( "LABEL_SAVE_IT", gettext( "Save changes" )  );
		$content->setVariable( "BACK_LINK", PATH_URL . "profile/index/" . $user->get_name() . "/" );
		$content->setVariable( "LABEL_GOTO_HOMEPAGE", "<a href=\"" . PATH_URL . "profile/index/" . $user->get_name() . "/\">" . gettext( "back" ) . "</a>" );
		if(ENABLED_USER_DESC){
			$content->setVariable( "LABEL_BB_BOLD", gettext( "B" ) );
			$content->setVariable( "HINT_BB_BOLD", gettext( "boldface" ) );
			$content->setVariable( "LABEL_BB_ITALIC", gettext( "I" ) );
			$content->setVariable( "HINT_BB_ITALIC", gettext( "italic" ) );
			$content->setVariable( "LABEL_BB_UNDERLINE", gettext( "U" ) );
			$content->setVariable( "HINT_BB_UNDERLINE", gettext( "underline" ) );
			$content->setVariable( "LABEL_BB_STRIKETHROUGH", gettext( "S" ) );
			$content->setVariable( "HINT_BB_STRIKETHROUGH", gettext( "strikethrough" ) );
			$content->setVariable( "LABEL_BB_IMAGE", gettext( "IMG" ) );
			$content->setVariable( "HINT_BB_IMAGE", gettext( "image" ) );
			$content->setVariable( "LABEL_BB_URL", gettext( "URL" ) );
			$content->setVariable( "HINT_BB_URL", gettext( "web link" ) );
			$content->setVariable( "LABEL_BB_MAIL", gettext( "MAIL" ) );
			$content->setVariable( "HINT_BB_MAIL", gettext( "email link" ) );

		}




		// PROFILE VALUES
		if(ENABLED_FIRST_NAME)
		$content->setVariable( "VALUE_USER_FIRSTNAME", $this->safe_string( $user_profile[ "USER_FIRSTNAME" ] ) );
		if(ENABLED_FULL_NAME)
		$content->setVariable( "VALUE_USER_FULLNAME", $this->safe_string( $user_profile[ "USER_FULLNAME" ] ) );

		/*
		 *  Assure translations for statuses are available via gettext
		 */

		gettext("student");gettext("staff member");gettext("guest");gettext("alumni");
		if(ENABLED_BID_DESCIPTION){
			$content->setVariable( "VALUE_STATUS_BID", $this->safe_string( $user_profile[ "OBJ_DESC" ] ) );
		}
		if(ENABLED_STATUS){
			$stati = array( "student", "staff member", "guest", "alumni" );
			foreach( $stati as $status )
			{
				$content->setCurrentBlock( "BLOCK_STATUS" );
				$content->setVariable( "VALUE_STATUS", $status );
				if ( $status === $user_profile[ "OBJ_DESC" ] )
				{
					$content->setVariable( "STATUS_SELECTED", 'selected="selected"' );
				}
				$content->setVariable( "VALUE_STATUS_TRANSLATED", secure_gettext( $status ) );
				$content->parse( "BLOCK_STATUS" );
			}
		}



		//TODO: TEMPLATE EDITIEREN
		if(ENABLED_GENDER)
		$content->setVariable( "GENDER_" . $this->safe_string( $user_profile[ "USER_PROFILE_GENDER" ], "X" ). "_CHECKED", 'checked="checked"' );

		$cache     = get_cache_function( "ORGANIZATION", 86400 );
		if(ENABLED_FACULTY){
			$faculties = $cache->call( "lms_steam::get_faculties_asc" );
			$content->setVariable( "LABEL_MISCELLANEOUS", gettext( "miscellaneous" ) );
			foreach( $faculties as $faculty )
			{
				$content->setCurrentBlock( "BLOCK_FACULTY" );
				$content->setVariable( "FACULTY_ID", $faculty[ "OBJ_ID" ] );
				if ( $user_profile[ "USER_PROFILE_FACULTY" ] == $faculty[ "OBJ_ID" ] )
				{
					$content->setVariable( "FACULTY_SELECTED", 'selected="selected"' );
				}
				$content->setVariable( "FACULTY_NAME", $faculty[ "OBJ_NAME" ] );
				$content->parse( "BLOCK_FACULTY" );
			}
		}
		if(ENABLED_MAIN_FOCUS)
		$content->setVariable( "VALUE_FOCUS", $this->safe_string( $user_profile[ "USER_PROFILE_FOCUS" ] ) );
		if(ENABLED_HOMETOWN)
		$content->setVariable( "VALUE_HOMETOWN", $this->safe_string( $user_profile[ "USER_PROFILE_HOMETOWN" ] ) );
		if(ENABLED_WANTS)
		$content->setVariable( "VALUE_WANTS", $this->safe_string( $user_profile[ "USER_PROFILE_WANTS" ] ) );
		if(ENABLED_HAVES)
		$content->setVariable( "VALUE_HAVES", $this->safe_string( $user_profile[ "USER_PROFILE_HAVES" ] ) );
		if(ENABLED_OTHER_INTERESTS)
		$content->setVariable( "VALUE_OTHER_INTERESTS", $this->safe_string( $user_profile[ "USER_PROFILE_OTHER_INTERESTS" ] ) );
		if(ENABLED_ORGANIZATIONS)
		$content->setVariable( "VALUE_ORGANIZATIONS", $this->safe_string( $user_profile[ "USER_PROFILE_ORGANIZATIONS" ] ) );
		if(ENABLED_USER_DESC)
		$content->setVariable( "VALUE_USER_PROFILE_DSC", $this->safe_string( $user_profile[ "USER_PROFILE_DSC" ] ) );
		if(ENABLED_EMAIL)
		$content->setVariable( "VALUE_EMAIL", $this->safe_string( $user_profile[ "USER_EMAIL" ] ) );
		if(ENABLED_ADDRESS )
		$content->setVariable( "VALUE_ADDRESS", $this->safe_string( $user_profile[ "USER_PROFILE_ADDRESS" ] ) );
		if(ENABLED_TELEPHONE)
		$content->setVariable( "VALUE_TELEPHONE", $this->safe_string( $user_profile[ "USER_PROFILE_TELEPHONE" ] ) );
		if(ENABLED_PHONE_MOBILE)
		$content->setVariable( "VALUE_PHONE_MOBILE", $this->safe_string( $user_profile[ "USER_PROFILE_PHONE_MOBILE" ] ) );
		if(ENABLED_WEBSITE){
			$content->setVariable( "VALUE_WEBSITE", $this->safe_string( $user_profile[ "USER_PROFILE_WEBSITE_URI" ] ) );
			$content->setVariable( "VALUE_WEBSITE_NAME", $this->safe_string( $user_profile[ "USER_PROFILE_WEBSITE_NAME" ] ) );

		}
		if(ENABLED_ICQ_NUMBER || ENABLED_BID_IM)
		$content->setVariable( "VALUE_IM_ICQ", $this->safe_string( $user_profile[ "USER_PROFILE_IM_ICQ" ] ) );
		if(ENABLED_SKYPE_NAME || ENABLED_BID_IM)
		$content->setVariable( "VALUE_IM_SKYPE", $this->safe_string( $user_profile[ "USER_PROFILE_IM_SKYPE" ] ) );
		if(ENABLED_AIM_ALIAS || ENABLED_BID_IM)
		$content->setVariable( "VALUE_IM_AIM", $this->safe_string( $user_profile[ "USER_PROFILE_IM_AIM" ] ) );
		if(ENABLED_MSN_IDENTIFICATION || ENABLED_BID_IM)
		$content->setVariable( "VALUE_IM_MSN", $this->safe_string( $user_profile[ "USER_PROFILE_IM_MSN" ] ) );
		if(ENABLED_YAHOO_ID || ENABLED_BID_IM)
		$content->setVariable( "VALUE_IM_YAHOO", $this->safe_string( $user_profile[ "USER_PROFILE_IM_YAHOO" ] ) );



		if(ENABLED_LANGUAGES){
			// LANGUAGE
			if ( TRUE ) { // && !empty($user_profile["USER_LANGUAGE"]) ) {
				$ulang = $user_profile["USER_LANGUAGE"];
				if (!is_string($ulang) || $ulang === "0") $ulang = LANGUAGE_DEFAULT_STEAM;
				$languages = array(
    		"english" => array("name" => gettext("English"), "icon" => "flag_gb.gif", "lang_key" => "en_US"),
    		"german"  => array("name" => gettext("German"), "icon" => "flag_de.gif", "lang_key" => "de_DE")
				);
				if (!array_key_exists($ulang, $languages)) {
					$ulang = LANGUAGE_DEFAULT_STEAM;
				}
				$content->setCurrentBlock("USER_LANGUAGE");
				$content->setVariable("LABEL_LANGUAGES", gettext("Language"));
				foreach( $languages as $key => $language) {
					$content->setCurrentBlock("LANGUAGE");
					$content->setVariable("LABEL_LANGUAGE_LABEL", "profile_language_" . $key);
					$content->setVariable("LANGUAGE_ICON", PATH_STYLE . "/images/" . $language["icon"]);
					$content->setVariable("LABEL_LANGUAGE", $language["name"]);
					$content->setVariable("LANGUAGE_VALUE", $key);
					if ( $ulang == $key ) {
						$content->setVariable("LANGUAGE_CHECKED", "checked=\"checked\"");
					}
					$content->parse("LANGUAGE");
				}
				$content->parse("USER_LANGUAGE");
			}
		}
		if(ENABLED_BID_LANGUAGE){
			$content->setVariable("LABEL_LANGUAGES", gettext("Language"));
			if(trim($user_profile["USER_LANGUAGE"]) == trim("german")){
				$content->setVariable("LANG1","Deutsch");
				$content->setVariable("LANG2","English");
				$content->setVariable("LANG3","german");
				$content->setVariable("LANG4", "english");
			}
			else{
				$content->setVariable("LANG1","English");
				$content->setVariable("LANG2","Deutsch");
				$content->setVariable("LANG3","english");
				$content->setVariable("LANG4", "german");

			}



		}
		if(ENABLED_BID_NAME){
			$content->setVariable("LABEL_BID_NAME", gettext("name"));
			$completeName = $user_profile["USER_FIRSTNAME"]." ".$user_profile["USER_FULLNAME"];
			$content->setVariable("VALUE_USER_NAME_BID", $completeName);

		}
		if(ENABLED_BID_ADRESS){
			$content->setVariable("LABEL_BID_ADDRESS", gettext("Address"));
			$content->setVariable("VALUE_BID_ADDRESS", $user->get_attribute("USER_ADRESS") ) ;
		}
		if(ENABLED_BID_EMAIL){
			$content->setVariable("LABEL_EMAIL_BID", gettext("E-Mail"));
			$content->setVariable("VALUE_EMAIL_BID", $user->get_attribute("USER_EMAIL"));

		}
		if(ENABLED_BID_PHONE){
			$content->setVariable("LABEL_TELEPHONE_BID", "Telefon");
			$content->setVariable("VALUE_TELEPHONE_BID",$user->get_attribute("bid:user_callto"));
		}
		
		if(PLATFORM_ID=="bid"){
			$frameResponseObject->setHeadline(array(
			array( "link" => PATH_URL . "home/",
			"name" => $user->get_attribute( "USER_FIRSTNAME" ) . " " . $user->get_attribute( "USER_FULLNAME" )
			),
			array( "link" => PATH_URL . "profile/",
			"name" => gettext( "Profile" )
			),
			array( "link" => "",
			"name" => "Profil ändern"
			)
			));

		}else{
			$frameResponseObject->setHeadline(array(
			array( "link" => PATH_URL . "profile/index/" . $user->get_name() . "/",
			"name" => $user->get_attribute( "USER_FIRSTNAME" ) . " " . $user->get_attribute( "USER_FULLNAME" )
			),
			array( "link" => "",
			"name" => gettext( "Profile" )
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