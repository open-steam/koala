<?php
include_once( "../etc/koala.conf.php" );
include_once( PATH_LIB . "steam_handling.inc.php" );
include_once( PATH_LIB . "format_handling.inc.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );

$is_sent = ((isset($_GET['sent']) && $_GET['sent'])?TRUE:FALSE);

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
{
  if (isset($_POST[ "delete" ]) && is_array($_POST[ "delete" ])) {
    $ids = array_keys( $_POST[ "delete" ] );
    foreach( $ids as $id )           
    {
      $message = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $id, CLASS_OBJECT );
      $message->delete(TRUE);
    }
    $GLOBALS["STEAM"]->buffer_flush();
    
    $no_messages = count( $ids );
    if ( $no_messages > 1 )
    {
      $portal->set_confirmation( str_replace( "%N", $no_messages, gettext( "%N messages deleted." ) ) );
    }
    else
    {
      $portal->set_confirmation( gettext( "1 message deleted." ) );
    }
  }
  else {
    $portal->set_problem_description( gettext( "No message selected." ) );
  }
}


$portal->set_page_title( gettext( "Mailbox" ) );

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "messages_mailbox.template.html" );

$content->setVariable( "INBOX_TEXT", gettext( "Your Inbox" ) );
$content->setVariable( "SENT_MAIL_TEXT", gettext( "Your Sent Mail" ) );

if(WRITE_MESSAGES || MAILBOX_KONFIGURATION || DELETE_MAILS){
	$content->setCurrentBlock( "BLOCK_MAILBOX_ACTIONBAR" );
	if (WRITE_MESSAGES){
		$content->setCurrentBlock( "BLOCK_WRITE_MESSAGES" );
		$content->setVariable( "LINK_COMPOSE_MESSAGE", PATH_URL . "messages_write.php" );
		$content->setVariable( "LABEL_COMPOSE_MESSAGE", gettext( "Compose a new message" ) );
		$content->parse( "BLOCK_WRITE_MESSAGES" );	
	}
	if (DELETE_MAILS){
		$content->setCurrentBlock( "BLOCK_DELETE_MESSAGES" );
		$content->setVariable( "LINK_DELETE_MESSAGES", PATH_URL . "messages.php?clear=true&sent=" . ( ($is_sent) ? "1" : "0" ) );
		$content->setVariable( "LABEL_DELETE_MESSAGES", ($is_sent) ? gettext( "Clear Sent Mails" ) : gettext( "Clear Inbox" ) );
		$content->parse( "BLOCK_DELETE_MESSAGES" );
	}
	if (MAILBOX_KONFIGURATION){
		$content->setCurrentBlock( "BLOCK_MAILBOX_KONFIGURATION" );
		$content->setVariable( "LINK_MAIL_PREFS", PATH_URL . "messages_prefs.php" );
		$content->setVariable( "LABEL_MAIL_PREFS", gettext( "Your mail preferences" ) );
		$content->parse( "BLOCK_MAILBOX_KONFIGURATION" );
	}
	$content->parse( "BLOCK_MAILBOX_ACTIONBAR" );
}

$user = steam_get_current_user();

if (isset($_GET['sent']) && $_GET['sent']) {
	$sent = 1;
	$sentfolder=$user->create_sent_mail_folder();
	$content->setVariable( "INBOX_MENU_CLASS", 'tabIn' );
    $content->setVariable( "OUTBOX_MENU_CLASS", 'tabOut' );
} else {
	$sent = 0;
	$sentfolder=false;
	$content->setVariable( "INBOX_MENU_CLASS", 'tabOut' );
    $content->setVariable( "OUTBOX_MENU_CLASS", 'tabIn' );
}

$messages = $user->get_mails(false,$sentfolder);
$no_messages = count( $messages );

if ( isset( $_GET['clear'] ) && $_GET['clear'] == "true" )
{
	foreach ( $messages as $msg )
	{
		$message = steam_factory::get_object( $GLOBALS[ "STEAM" ]->get_id(), $msg->get_id(), CLASS_OBJECT );
		$message->delete(TRUE);
	}
	
	$GLOBALS["STEAM"]->buffer_flush();
	
	if ( $no_messages > 1 )
    {
		$portal->set_confirmation( str_replace( "%N", $no_messages, gettext( "%N messages deleted." ) ) );
    }
    else if ( $no_messages == 1 )
    {
		$portal->set_confirmation( gettext( "1 message deleted." ) );
    }
  	else
  	{
		$portal->set_problem_description( gettext( "No messages to delete." ) );
  	}
  	
  	$no_messages = 0;
}

if ( $no_messages > 0  ) {
  $uri_params = "";
  if (isset($_GET["sent"]) && $_GET["sent"]) $uri_params = "?sent=1";
	$start = $portal->set_paginator( $content, 20, $no_messages, "(" . gettext( "%TOTAL messages in mailbox" ) . ")", $uri_params );
	$end = ( $start + 20 > $no_messages ) ? $no_messages : $start + 20;

	if ($sent == 0)
	{
		$content->setVariable( "DATE", gettext( "received" ) );
		$content->setVariable( "SENDER", gettext( "Sender" ) );
	}
	else
	{
		$content->setVariable( "DATE", gettext( "sent" ) );
		$content->setVariable( "SENDER", gettext( "Receiver" ) );
	}
	
	$content->setCurrentBlock( "BLOCK_MESSAGES_AVAILABLE" );
	$content->setVariable( "CONFIRMATION_MESSAGE", gettext( "Are you sure you want delete this?" ) );
	$content->setVariable( "SUBJECT", gettext( "Subject" ) );
	$content->setVariable( "SELECT_ALL", gettext( "Select all" ) );
	$content->setVariable( "DELETE", gettext( "Delete" ) );

  $attributequery = array(OBJ_NAME, OBJ_CREATION_TIME);
  if ($is_sent) $attributequery[] = "mailto"; // Query the receivers too
  
  $data_tnr = array();
	for( $i = $start; $i < $end; $i++ )
	{
    $data_tnr[$i] = array();
		$message = $messages[ $i ];
		$data_tnr[$i]["IS_READ"] = lms_steam::is_reader( $message, $user, TRUE );
		$data_tnr[$i]["CREATOR"]  = $message->get_creator(TRUE);
		$data_tnr[$i]["ATTRIBUTES"] = $message->get_attributes($attributequery, TRUE);
	}
  $data_result = $GLOBALS["STEAM"]->buffer_flush();

  $message_sender_html = array();
  if (!$is_sent) {
    $sender_tnr = array();
    for( $i = $start; $i < $end; $i++ )
    {
      $sender_tnr[$i] = $data_result[$data_tnr[$i]["CREATOR"]]->get_attributes(array(OBJ_NAME, OBJ_ICON, USER_FIRSTNAME, USER_FULLNAME), TRUE);
    }
    $sender_result = $GLOBALS["STEAM"]->buffer_flush();
    for( $i = $start; $i < $end; $i++ ) {
      $message = $messages[ $i ];
      $message_unread = !$data_result[$data_tnr[$i]["IS_READ"]];
      $unread_start = $message_unread ? "<strong>" : "";
      $unread_end = $message_unread ? "</strong>" : "";
      $senderlogin = $sender_result[$sender_tnr[$i]]["OBJ_NAME"];
      $senderurl = PATH_URL . "user/" . $senderlogin . "/";
      $sender_full_name = $sender_result[$sender_tnr[$i]]["USER_FIRSTNAME"] . " " . $sender_result[$sender_tnr[$i]]["USER_FULLNAME"];
      $sender_icon_html = "";
      if (is_object($sender_result[$sender_tnr[$i]][OBJ_ICON])) {
        $sender_icon_html = "<a href=\"" . $senderurl . "\"><img src=\"" . PATH_URL . "cached/get_document.php?id=" . $sender_result[$sender_tnr[$i]][OBJ_ICON]->get_id() . "&type=usericon&width=30&height=40" . "\" width=\"24\" height=\"28\" align=\"absmiddle\"></a>&nbsp;";
      }
      $message_sender_html[$i] =  $sender_icon_html . "<a href=\"" . $senderurl . "\">" . $unread_start . $sender_full_name . $unread_end . "</a>";
    }
  } else {
    // for caching cumulated receiver information for all receivers extracted
    // from "mailto" attribute (if thery are in object-arrays)
    $receiver_mapping = array();
    $mailtos = array();
    for( $i = $start; $i < $end; $i++ ) {
      $mailto = $data_result[$data_tnr[$i]["ATTRIBUTES"]]["mailto"];
      // ensure that mailto is an array always
      if (!is_array($mailto)) $mailto = array( $mailto );      
      // store array for further use
      $mailtos[$i] = $mailto;
      foreach($mailto as $receiver) {
        if (is_object($receiver)) {
          $receiver_mapping[$i . "-" . $receiver->get_id()] = $receiver;
        }
      }
    }
    // get needed attributes of the receivers at once
    steam_factory::load_attributes($GLOBALS["STEAM"]->get_id(), array_values($receiver_mapping), array(OBJ_NAME, OBJ_ICON, OBJ_TYPE));
// Optimize server calls if receiver is a course
    $coursecount = 0;
    $course_tnr = array();
    for( $i = $start; $i < $end; $i++ ) {
      $mailto = $mailtos[$i];
      $receiver = $mailto[0];
      if (is_object($receiver)) {
        if ($receiver instanceof steam_object) {
          if ($receiver instanceof steam_group) {
            $grouptype=$receiver->get_attribute(OBJ_TYPE);
            if (!is_string($grouptype)) $grouptype = "";
            if (stristr($grouptype, "course") || stristr($grouptype, "group_course")) {
              $course_tnr[$receiver->get_id()] = $receiver->get_parent_group(TRUE);
              $coursecount++;
            }
          }
        }
      }
    }
    if ($coursecount > 0) {
      $coursename_tnr = array();
      $course_result = $GLOBALS["STEAM"]->buffer_flush();
      $courseids = array_keys($course_tnr);
      foreach( $courseids as $id) {
        $coursename_tnr[$id] = $course_result[$course_tnr[$id]]->get_name(TRUE);
      }
      $coursename_result = $GLOBALS["STEAM"]->buffer_flush();
    }
    // construct the HTML output for the field "receiver"
    for( $i = $start; $i < $end; $i++ ) {
      $mailto = $mailtos[$i];
      $receiver = $mailto[0];
      if (is_object($receiver)) {
        $senderlogin = $receiver->get_name();
        $display_withlink = FALSE;
        if ($receiver instanceof steam_object) {
          if ($receiver instanceof steam_user) {
            $display_withlink = TRUE;
            $senderurl = PATH_URL . "user/" . $senderlogin . "/";
          } else if ($receiver instanceof steam_group) {
            $grouptype=$receiver->get_attribute(OBJ_TYPE);
            if (!is_string($grouptype)) $grouptype = "";
            if (stristr($grouptype, "course") || stristr($grouptype, "group_course")) {
              $display_withlink = TRUE;
              // XXX Optimize here (for students, this may be called very often)
              //$senderurl = PATH_URL . SEMESTER_URL . "/" . $receiver->get_parent_group()->get_name() .  "/" . $receiver->get_name() . "/";
              $senderurl = PATH_URL . SEMESTER_URL . "/" . $coursename_result[$coursename_tnr[$receiver->get_id()]] .  "/" . $receiver->get_name() . "/";
            } else  if (stristr($grouptype, "group_")) {
              $display_withlink = TRUE;
              $senderurl = PATH_URL . "groups/" . $receiver->get_id() . "/";
            }
          }
        }
        // display using a link for users and koala groups
        $receiver_icon = $receiver->get_attribute(OBJ_ICON);
        if (is_object($receiver_icon)) {
          $receiver_icon_html = "<img src=\"" . PATH_URL . "cached/get_document.php?id=" . $receiver_icon->get_id() . "&type=usericon&width=30&height=40" . "\" width=\"24\" height=\"28\" align=\"absmiddle\" />";
        } else {
          $receicer_icon_html = "<img src=\"null" . "\" width=\"24\" height=\"28\" align=\"absmiddle\" />";
        }
        if ($display_withlink) {
          $receiver_attributes = $receiver->get_attributes(array(USER_FIRSTNAME, USER_FULLNAME));
          $receiver_full_name = $receiver_attributes["USER_FIRSTNAME"] . " " . $receiver_attributes["USER_FULLNAME"];
          $message_sender_html[$i] = "<a href=\"" . h($senderurl) . "\">" . $receiver_icon_html ."</a>&nbsp;<a href=\"" . h($senderurl) . "\">" . h($receiver_full_name) . "</a>";
        } else  {
            $message_sender_html[$i] = $receiver_icon_html . "&nbsp;" . h($receiver_full_name);
        }
      } else if (is_string($receiver)) {
        $message_sender_html[$i] = h($receiver);
      } else {
        $message_sender_html[$i] = gettext("N.A.");
        error_log("messages.php: found invalid receiver type in 'mailto' of object with id=" . $messages[$i]->get_id());
      }
      if (sizeof($mailtos[$i])>1) {
        $message_sender_html[$i] .= ", [...]";
      }
    }
  }

	for( $i = $start; $i < $end; $i++ )
	{
    $message = $messages[ $i ];
    $message_unread = !$data_result[$data_tnr[$i]["IS_READ"]];
    $unread_start = $message_unread ? "<strong>" : "";
    $unread_end = $message_unread ? "</strong>" : "";
		$content->setCurrentBlock( "BLOCK_MESSAGE" );
		$content->setVariable( "MESSAGE_RECEIVER", $message_sender_html[$i] );
		$content->setVariable( "MESSAGE_READ_LINK", PATH_URL . "message_read.php?id=" . $message->get_id().($sent?'&sent=1':'') );
		$subject = $data_result[$data_tnr[$i]["ATTRIBUTES"]][OBJ_NAME];
		if ( strlen( $subject ) > 70 ) {
			$cut_index = 67;
			while ( ord($subject[ $cut_index ]) > 127 && $cut_index > 0 ) $cut_index--;
			if ( $cut_index > 0 ) $subject = substr( $subject, 0, $cut_index ) . '...';
		}
		if ( $message_unread )
		{
			$content->setVariable( "MESSAGE_READ_ICON", PATH_STYLE . "images/mail_new.png" );
			$content->setVariable( "MESSAGE_ALT_TAG", gettext( "You haven't read this message yet." ) );
		}
		else
		{
			$content->setVariable( "MESSAGE_READ_ICON", PATH_STYLE . "images/mail_generic.png" );
			$content->setVariable( "MESSAGE_ALT_TAG", gettext( "You have read this message." ) );
		}
		$content->setVariable( "MESSAGE_SUBJECT", $unread_start . h($subject) . $unread_end  );
		$timestamp = $data_result[$data_tnr[$i]["ATTRIBUTES"]][OBJ_CREATION_TIME];
		$content->setVariable( "MESSAGE_TIMESTAMP", $unread_start . strftime( "%x", $timestamp ) . " " . strftime( "%H:%M", $timestamp) . $unread_end );
		$content->setVariable( "MESSAGE_ID", $message->get_id() );
		$content->parse( "BLOCK_MESSAGE" );
	}
	$content->parse( "BLOCK_MESSAGES_AVAILABLE" );
}
else
{
	$content->setCurrentBlock( "BLOCK_NO_MESSAGES" );
	$content->setVariable( "NO_MSG_TEXT", gettext( "There are no messages for you." ) );
	$content->parse( "BLOCK_NO_MESSAGES" );
}
$portal->set_page_main(
		array(
			array( "link" => PATH_URL . "messages.php", "name" => gettext( "mailbox" ) ),
			array( "", "name" => $sent ? gettext("Your Sent Mail") : gettext("Your Inbox") )
		 ),
		$content->get(),
		""
		);
$portal->show_html();
?>
