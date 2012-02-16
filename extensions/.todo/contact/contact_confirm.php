<?php
include_once( "../etc/koala.conf.php" );
include_once( PATH_LIB . "sort_functions.inc.php" );


$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$portal->set_page_title( gettext( "Confirm contact" ) );
$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "contact_confirm.template.html" );

$user = lms_steam::get_current_user();

$id = ( ! empty( $_GET[ "id" ] ) ) ? $_GET[ "id" ] : $_POST[ "id" ];
$contact = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $id, CLASS_USER );
$contact_attributes = $contact->get_attributes( 
								array( "USER_FIRSTNAME", "USER_FULLNAME" ) 
								);
                
  if ( ! $contact instanceof steam_user )
  {
          include( "bad_link.php" );
          exit;
  }
  
  if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
  {
    if (isset($_POST["confirm"])) {
      $user_attributes = $user->get_attributes( array( "USER_FIRSTNAME", "USER_FULLNAME", "USER_FAVOURITES", "USER_EMAIL" ) );
      $buddies = $user_attributes[ "USER_FAVOURITES" ];
      $is_buddy = FALSE;
      if ( ! is_array( $buddies ) )
              $buddies = array();
      foreach( $buddies as $buddy )
      {
              if ( ( is_object( $buddy ) ) && ( $buddy->get_id() == $id ) )
              {
                      //throw new Exception( "User is in buddy list yet." );
                      $is_buddy = TRUE;
              }
      }
      if (!$is_buddy) {
        $buddies[] = $contact;
        $user->set_attribute( "USER_FAVOURITES", $buddies );
      }
      $contact->contact_confirm();
      logging::write_log( LOG_MESSAGES, "CONFIRMED\t" . $user->get_name() . "\t" . $contact->get_name() );

      $message = str_replace( "%NAME", $user_attributes[ "USER_FIRSTNAME" ] . " " . $user_attributes[ "USER_FULLNAME" ], gettext( "%NAME has confirmed you as a contact." ) );
      $message .= " " . gettext( "You can visit her/his profile here:" );
      $message .= " <a href=\"" . PATH_URL . "user/" . $user->get_name() . "/\">";
      $message .= str_replace( "%NAME", $user_attributes[ "USER_FIRSTNAME" ] . " " . $user_attributes[ "USER_FULLNAME" ] , gettext( "%NAME's profile" ) ) . "</a>";

      //$contact->mail( "LLMS: " . str_replace( "%NAME", $user_attributes[ "USER_FIRSTNAME" ] . " " . $user_attributes[ "USER_FULLNAME" ], gettext( "%NAME has confirmed your contact" ) ), $message, $user_attributes[ "USER_EMAIL" ] );
      lms_steam::mail($contact, $user, PLATFORM_NAME . ": " . str_replace( "%NAME", $user_attributes[ "USER_FIRSTNAME" ] . " " . $user_attributes[ "USER_FULLNAME" ], gettext( "%NAME has confirmed your contact" ) ), $message);

      // require_once( "Cache/Lite.php" );
      // $cache = new Cache_Lite( array( "cacheDir" => PATH_CACHE ) );
      $cache = get_cache_function( $user->get_name() );
      $cache->clean( $user->get_name() );
      $cache->clean( $user->get_id());
      $cache = get_cache_function( $contact->get_name() );
      $cache->clean( $contact->get_name() );
      $cache->clean( $contact->get_id());
      $_SESSION["confirmation"] = str_replace("%NAME", $contact->get_name(), gettext( "Confirmed contact request from %NAME" ));
    } else if (isset($_POST["deny"])) {
      $_SESSION["confirmation"] = str_replace("%NAME", $contact->get_name(), gettext( "Denied contact request from %NAME" ));
    }
    // remove contact from the "to confirm" list
    $toconfirm = $user->get_attribute("USER_CONTACTS_TOCONFIRM");
    if (!is_array($toconfirm)) $toconfirm = array();
    $newtc = array();
    foreach($toconfirm as $tc) {
      if (!is_object($user) || $tc->get_id() == $contact->get_id() ) continue;
      $newtc[] = $tc;
    }
    $user->set_attribute("USER_CONTACTS_TOCONFIRM", $newtc);

    // Cache leeren
		require_once( 'Cache/Lite.php' );
		$cache = new Cache_Lite( array( "cacheDir" => PATH_CACHE ) );
		$cache = get_cache_function( $user->get_name() );
		$cache->clean( $user->get_name() );
		$cache->clean( $user->get_id());

    //Redirect
    header( "Location: " . $_POST[ "backlink" ]);
    exit;
  }
  
  $content->setVariable( "CONTACT_ID", $contact->get_id());
  $content->setVariable( "INFO_TEXT", gettext( "Confirmed contacts will show up in your contact list. They have read access on your contact details in your profile."  ) );
  
  $content->setVariable( "LABEL_OK", gettext( "Confirm" ) );
  $content->setVariable( "LABEL_NOTOK", gettext( "Deny" ) );
  $content->setVariable( "BACK_LINK", PATH_URL . "user/" . $user->get_name() . "/contacts/" );
  $content->setVariable( "BACKLINK_TEXT", "<a href=\"" . PATH_URL . "user/" . $user->get_name() . "/contacts/\">" . str_replace( "%NAME", $contact_attributes[ "USER_FIRSTNAME" ] . " " . $contact_attributes[ "USER_FULLNAME" ], gettext( "cancel and go back to Contact list." ) ). "</a>" );
  $content->parse( "BLOCK_FORM" );

$portal->set_page_main(
								array( array( "link" => "", "name" => str_replace( "%NAME", $contact_attributes[ "USER_FIRSTNAME" ] . " " . $contact_attributes[ "USER_FULLNAME" ], gettext( "Confirm %NAME as a contact?" ) ) ) ),
								$content->get(),
								"ThinCase"
								);
$portal->show_html();
?>
