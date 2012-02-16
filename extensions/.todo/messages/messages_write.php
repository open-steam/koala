<?php
include_once( "../etc/koala.conf.php" );
include_once( PATH_LIB . "sort_functions.inc.php" );
require_once( PATH_LIB . "format_handling.inc.php" );

if ( !isset($portal) || !is_object($portal) )
{
	$portal = lms_portal::get_instance();
	$portal->initialize( GUEST_NOT_ALLOWED );
}

$user = lms_steam::get_current_user();
$em = lms_steam::get_extensionmanager();

@$backlink = ( empty( $_POST["values"]["backlink"] ) ) ? $_SERVER[ "HTTP_REFERER" ] : $_POST[ "values" ][ "backlink" ];

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
{

  /*
   *
   *     Sending tha mails
   *
   */


  // Data has been sent by POST and is now ready to be processed


  $values = $_POST[ "values" ];

  $success=false;  //Set to true if at least one message has been sent
  $error=false;	   //Set to true if an error occurs

  // Receivers are sent as an array $values['receiver'] of object-ids
  // which is processed step by step. 0-values are left out as they represent no selected receiver

  foreach (array_unique($values['receiver']) as $recieverID){
   if ($recieverID) {
    if ( !empty($values[ "subject" ])) {
        //get the actual object
        $receiver = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $recieverID );
        $footer = "";
        $subject = $values["subject"];
        if ( $receiver instanceof steam_group ) {
          // if the object the mail is sent to is some kind of group, we have to
          // get some further information which should be sent to the recievers
          $type = (string) $receiver->get_attribute( "OBJ_TYPE" );
          // Creating object of different types by the OBJ_TYPE
          switch( $type ) {
            case "course":
              $lms_grp = new koala_group_course( $receiver );
            break;
            default:
              //////////////////////////////////////////////////////////////
              // look for group objects in extensions
              $extension = $em->get_extension_by_obj_type($type);
              if ( is_object($extension) )
              {
                $lms_grp = $extension->get_wrapper_class( $receiver );
              }
              else $lms_grp = new koala_group_default( $receiver );
              break;
          }
          // build the footer
          $footer = "<br /><br />--- <br />" . gettext("This E-Mail was sent to %GROUP in koaLA.");
          $footer = str_replace( "%GROUP", $lms_grp->get_display_name(), $footer );
          $subject = gettext( "Circular" ) . ": " . $subject;
        }
        $body = $values[ "body" ];
        // Prevent the usage of HTML-Code in Mail body and subject as this is a possible
        // security issue when mails are shown in a browser
        $body = str_replace('<','&lt;',$body);
        $body = str_replace('>','&gt;',$body);
        $subject = str_replace('<','&lt;',$subject);
        $subject = str_replace('>','&gt;',$subject);
        if (isset($footer)) {
          $body .= $footer;
        }

        // Finally, we are sending the mail and set success to true
        lms_steam::mail($receiver, $user, $subject, $body);
        $success=true;
    } else {
      if (!$error) $portal->set_problem_description(gettext("Please enter a subject for your message."));
      $error=true;
    }
   }
  }

  if ($success){

  	// If we have sent an email, we can send the user to where he came from and inform him about this

    $_SESSION[ "confirmation" ] = str_replace( "%NAME", $receiver->get_name(), gettext( "Mail has been sent"));
    header( "Location: " . $values[ "backlink" ] );
  }
  else {
    if (!$error) {
    	$portal->set_problem_description(gettext("Please select a receiver!"));
    	$error=true;
    }
  }

}

/*
 *
 *   Building the web interface
 *
 */

$portal->set_page_title( gettext( "Mail" ) );
$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "messages_write.template.html" );

// Set information about the sender

$content->setVariable( "LABEL_FROM", gettext( "From" ) );
$user_icon = ( $user->get_attribute( "OBJ_ICON" ) === 0 ) ? PATH_STYLE . "images/anonymous.jpg" : PATH_URL . "cached/get_document.php?id=" . $user->get_attribute( "OBJ_ICON" )->get_id() . "&type=usericon&width=30&height=40";
$content->setVariable( "SENDER_IMAGE", $user_icon );
$content->setVariable( "SENDER_NAME", $user->get_attribute( "USER_FIRSTNAME" ) . " " . $user->get_attribute( "USER_FULLNAME" ) );
$content->setVariable( "LABEL_TO", gettext( "To" ) );
$content->setVariable( "LABEL_SUBJECT", gettext( "Subject") );
$content->setVariable( "LABEL_BODY", gettext("Message") );

// Handle reply

if ( !empty($_GET[ "reply_to" ]) && $_GET[ "reply_to" ] > 0 )
{
   $message = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $_GET[ "reply_to" ] );
   $content->setVariable( "VALUE_SUBJECT", "Re: " . $message->get_name() );
   $body = $message->get_content();
   $body = str_replace('<br />',"\n",$body);
   $body = explode( "\n", $body );
   $new_body = "";
   $start = 2; $end = count( $body ) - 2;
   for ( $i = $start; $i < $end; $i++ )
   {
      $line = $body[ $i ];
      $new_body .= "> " . $line . "\n";
   }
   $signature = $user->get_attribute( "USER_EMAIL_SIGNATURE" );
   if ( !is_string($signature) ) $signature = "";
   $signature = ( empty( $signature ) ) ? "" : $signature . "\n\n";
   $content->setVariable( "VALUE_BODY", "\n\n" . $signature. $new_body  );
   $mail_headers = $message->get_attribute( "MAIL_MIMEHEADERS" );
   if ( ! is_array( $mail_headers ) )
   $mail_headers = array();

   $receiver = $message->get_creator();
   /**  UM RUNDMAILS IN KURSEN UASZUSCHLIESSEN, DIE DURCH ANTWORTENDE STUDENTEN ERZEUGT WERDEN
   if ( array_key_exists( "X-Steam-Group", $mail_headers ) )
   {
      $groupname = $mail_headers[ "X-Steam-Group" ];
      $receiver  = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $groupname );
   }
   else
   {
      $receiver = $message->get_creator();
   }
   **/
}
else
{

  //set the message subject and body

  $subject = (isset($values[ "subject" ])? $values[ "subject" ] :"");
  $text = (isset($values[ "body" ])?$values[ "body" ] :"");
	$signature = $user->get_attribute( "USER_EMAIL_SIGNATURE" );
	if ( !is_string($signature) ) $signature = "";
  else $signature = "\n\n" . $signature;
  if (empty($new_body)) $new_body = "";
  if ($_SERVER[ "REQUEST_METHOD" ] == "POST" ) {
    $content->setVariable( "VALUE_SUBJECT", $subject);
    $content->setVariable( "VALUE_BODY", $text);
  }
  else $content->setVariable( "VALUE_BODY", $signature . $new_body  );
}

	// selection of the recievers
	// if a buddy is given, there is no selection
	// if a group is given, users can select the whole group or a set of members
	// else users can select one or more of their buddies or groups

if ( ! empty( $_GET[ "to" ] ) || (isset($receiver) && $receiver instanceof steam_user ))
{
   // buddy given

   $content->setCurrentBlock( "BLOCK_BUDDY_GIVEN" );
   if ( !isset($receiver) )
   {
      $receiver = steam_factory::username_to_object( $GLOBALS[ "STEAM" ]->get_id(), $_GET[ "to" ] );
   }
   $content->setVariable( "RECEIVER_NAME", $receiver->get_attribute( "USER_FIRSTNAME" ) . " " . $receiver->get_attribute( "USER_FULLNAME" ) );
   $icon_link = ( $receiver->get_attribute( OBJ_ICON ) === 0 ) ? PATH_STYLE . "images/anonymous.jpg" : PATH_URL . "get_document.php?id=" . $receiver->get_attribute( OBJ_ICON )->get_id() . "&type=usericon&width=30&height=40";
   $content->setVariable( "RECEIVER_IMAGE", $icon_link );
   $content->setVariable( "RECEIVER_OBJ_ID", $receiver->get_id() );
   $content->parse( "BLOCK_BUDDY_GIVEN" );
}
elseif( ! empty( $_GET[ "group" ] ) || (isset ($receiver) && $receiver instanceof steam_group ))
{
   // group given

   $content->setCurrentBlock( "BLOCK_GROUP_GIVEN" );
   $content->setVariable( "LABEL_SELECT_MEMBERS", gettext('Select members') );
   if ( !isset($receiver) || ! $receiver )
   {
      $receiver = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $_GET[ "group" ] );
   }
   if ( ! $receiver instanceof steam_group )
   {
      throw new Exception( "Group " . $_GET[ "group" ] . " does not exist." );
   }
    $cache = get_cache_function( $receiver->get_id(), CACHE_LIFETIME_STATIC );
	$type = (string) $receiver->get_attribute( "OBJ_TYPE" );
	switch( $type )
	{
		case ( "course" ):
        $content->setVariable( "LABEL_WHOLE_GROUP", gettext('Whole course') );
        $receiver = new koala_group_course( $receiver );
        $members = $cache->call( "lms_steam::group_get_members", $receiver->steam_group_learners->get_id() );
        $staffmembers = $cache->call( "lms_steam::group_get_members", $receiver->steam_group_staff->get_id() );
        $content->setVariable( "RECEIVER_NAME", h($receiver->get_name()) . " - " . h($receiver->get_attribute( "OBJ_DESC" )) );
		break;
	    default:
        $content->setVariable( "LABEL_WHOLE_GROUP", gettext('Whole group') );
        $members = $cache->call( "lms_steam::group_get_members", $receiver->get_id() );
				$content->setVariable( "RECEIVER_NAME", h($receiver->get_name()) );
		break;
}

   $icon_link = ( $receiver->get_attribute( OBJ_ICON ) === 0 ) ? PATH_STYLE . "images/anonymous.jpg" : PATH_URL . "get_document.php?id=" . $receiver->get_attribute( OBJ_ICON )->get_id() . "&type=usericon&width=30&height=40";
   $content->setVariable( "RECEIVER_IMAGE", $icon_link );
   $content->setVariable( "RECEIVER_OBJ_ID", $receiver->get_id() );

   $content->setVariable( "SELECT", gettext( "Select" ) );
   usort( $members, "sort_buddies" );

   if (is_array($staffmembers)) { // course
     usort( $staffmembers, "sort_buddies" );
     $content->setCurrentBlock("BLOCK_OPTGROUP");
     $content->setVariable("OPTGROUP_NAME", gettext("Participants") );
     foreach( $members as $buddy ) {
        $content->setCurrentBlock( "BLOCK_OPTGROUP_MEMBERS" );
        $content->setVariable( "OM_USER_ID", $buddy[ "OBJ_ID" ] );
        $content->setVariable( "OM_USER_FULLNAME" , $buddy[ "USER_FULLNAME" ]);
        $content->setVariable( "OM_USER_FIRSTNAME" , $buddy[ "USER_FIRSTNAME" ]);
        $content->parse( "BLOCK_OPTGROUP_MEMBERS" );
     }
     $content->parse("BLOCK_OPTGROUP");
     $content->setCurrentBlock("BLOCK_OPTGROUP");
     $content->setVariable("OPTGROUP_NAME", gettext("Staff members") );
     foreach( $staffmembers as $buddy ) {
        $content->setCurrentBlock( "BLOCK_OPTGROUP_MEMBERS" );
        $content->setVariable( "OM_USER_ID", $buddy[ "OBJ_ID" ] );
        $content->setVariable( "OM_USER_FULLNAME" , $buddy[ "USER_FULLNAME" ]);
        $content->setVariable( "OM_USER_FIRSTNAME" , $buddy[ "USER_FIRSTNAME" ]);
        $content->parse( "BLOCK_OPTGROUP_MEMBERS" );
     }
     $content->parse("BLOCK_OPTGROUP");
   } else { // group
     foreach( $members as $buddy )
     {
        $content->setCurrentBlock( "BLOCK_MEMBERS" );
        $content->setVariable( "USER_ID", $buddy[ "OBJ_ID" ] );
        $content->setVariable( "USER_FULLNAME" , $buddy[ "USER_FULLNAME" ]);
        $content->setVariable( "USER_FIRSTNAME" , $buddy[ "USER_FIRSTNAME" ]);
        $content->parse( "BLOCK_MEMBERS" );
     }
   }
   $content->setVariable( "LABEL_ADD_RECIEVER", gettext( "Add another reciever" ) );
   $content->parse( "BLOCK_GROUP_GIVEN" );

}
else
{
   // Nothing given: Selection if buddies and groups
   if(empty($values))
   {
	   $content->setCurrentBlock( "BLOCK_SELECT_BUDDY" );
	   $content->setVariable( "SELECT", gettext( "Select" ) );
	   $cache = get_cache_function( $user->get_name(), 86400 );
	   $buddies = $cache->call( "lms_steam::user_get_buddies", $user->get_name() );
	   usort( $buddies, "sort_buddies" );

	   $content->setVariable( "LABEL_BUDDIES", gettext( "your contacts" ) );
	   foreach( $buddies as $buddy )
	   {
	      $content->setCurrentBlock( "BLOCK_BUDDY" );
	      $content->setVariable( "USER_ID", $buddy[ "OBJ_ID" ] );
	      $content->setVariable( "USER_FULLNAME" , $buddy[ "USER_FULLNAME" ]);
	      $content->setVariable( "USER_FIRSTNAME" , $buddy[ "USER_FIRSTNAME" ]);
	      $content->parse( "BLOCK_BUDDY" );
	   }
	   $content->setVariable( "LABEL_GROUPS", gettext( "your groups" ) );
	   $content->setVariable( "LABEL_ADD_RECIEVER", gettext( "Add another reciever" ) );

	   $groups = lms_steam::user_get_groups( $user->get_name(), FALSE );
	   usort( $groups, "sort_objects" );

	   foreach( $groups as $group )
	   {
	      $content->setCurrentBlock( "BLOCK_GROUP" );
	      $content->setVariable( "GROUP_ID", $group[ "OBJ_ID" ] );
	      $content->setVariable( "GROUP_NAME", $group[ "OBJ_NAME" ] );
	      $content->parse( "BLOCK_GROUP" );
	   }
		$content->setVariable("DISPLAY", "style=\"display:none\"");
	   $content->parse( "BLOCK_SELECT_BUDDY" );
   }
   else
   {
   		// we have post data and reconstruct the recievers list
   		$receivers = array_unique($values['receiver']);
   		foreach ($receivers as $receiverID)
   		{
   			if( $receiverID )
   			{
        		$receiver = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $receiverID );

    		   	$content->setCurrentBlock( "BLOCK_SELECT_BUDDY" );
   			   	$content->setVariable( "SELECT", gettext( "Select" ) );
   				$cache = get_cache_function( $user->get_name(), 86400 );
   				$buddies = $cache->call( "lms_steam::user_get_buddies", $user->get_name() );
   				usort( $buddies, "sort_buddies" );

   				$content->setVariable( "LABEL_BUDDIES", gettext( "your contacts" ) );
			   foreach( $buddies as $buddy )
			   {
			      $content->setCurrentBlock( "BLOCK_BUDDY" );
			      $content->setVariable( "USER_ID", $buddy[ "OBJ_ID" ] );
			      if( $receiver instanceof steam_user && $receiver->get_id() == $buddy[ "OBJ_ID" ] ) $content->setVariable("USER_SELECTED", "selected=\"selected\"");
			      $content->setVariable( "USER_FULLNAME" , $buddy[ "USER_FULLNAME" ]);
			      $content->setVariable( "USER_FIRSTNAME" , $buddy[ "USER_FIRSTNAME" ]);
			      $content->parse( "BLOCK_BUDDY" );
			   }
			   $content->setVariable( "LABEL_GROUPS", gettext( "your groups" ) );
			   $content->setVariable( "LABEL_ADD_RECIEVER", gettext( "Add another reciever" ) );

			   $groups = lms_steam::user_get_groups( $user->get_name(), FALSE );
			   usort( $groups, "sort_objects" );

			   foreach( $groups as $group )
			   {
			      $content->setCurrentBlock( "BLOCK_GROUP" );
			      $content->setVariable( "GROUP_ID", $group[ "OBJ_ID" ] );
			      if( $receiver instanceof steam_group && $receiver->get_id() == $group[ "OBJ_ID" ] ) $content->setVariable("GROUP_SELECTED", "selected=\"selected\"");
			      $content->setVariable( "GROUP_NAME", $group[ "OBJ_NAME" ] );
			      $content->parse( "BLOCK_GROUP" );
			   }
				if( $receiverID != $receivers[sizeof($receivers)-1]) $content->setVariable("DISPLAY", "style=\"display:none\"");
				else $content->setVariable("DISPLAY", "style=\"display:inline\"");
			   $content->parse( "BLOCK_SELECT_BUDDY" );
   			}
   		}
	}
}
/**
	$content->setVariable( "LABEL_ATTACHMENTS", gettext( "Attachments" ) );
	$content->setVariable( "LABEL_CHOOSE_ATTACHMENT", str_replace( "%RUCKSACK", "<a href=\"" . PATH_URL . "\">" . gettext( "rucksack" ) . "</a>", gettext( "Choose a file from your computer or your %RUCKSACK" ) ) );
 **/
// TEST
/**
	$content->setCurrentBlock( "BLOCK_ATTACHMENTS" );
	$content->setCurrentBlock( "BLOCK_ATTACHMENT" );
	$content->setVariable( "FILE_NAME", "praesentation.doc" );
	$content->setVariable( "FILE_SIZE", "13kb" );
	$content->parse( "BLOCK_ATTACHMENT" );
	$content->setVariable( "LABEL_ALL", gettext( "all" ) );
	$content->setVariable( "FILE_SIZE_ALL", "13kb" );
	$content->setVariable( "LABEL_REMOVE_ATTACHMENTS", gettext( "remove selected attachments") );
	$content->parse( "BLOCK_ATTACHMENTS" );
	$content->setVariable( "LABEL_UPLOAD", gettext( "upload" ) );
 **/

$content->setVariable( "LABEL_SEND_THIS", gettext( "Send this" ) );

$content->setVariable( "VALUE_BACKLINK", $backlink );
$content->setVariable( "BACKLINK", "<a href=\"$backlink\">" . gettext( "back" ) . "</a>" );
$portal->set_page_main(
array( array( "link" => (YOUR_MAILBOX) ? PATH_URL . "messages.php" : "", "name" => gettext("mailbox") ), array( "link" => "", "name" => gettext( "new message" ) ) ),
$content->get(),
"ThinCase");

$portal->show_html();
?>
