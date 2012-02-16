<?php
include_once( "../etc/koala.conf.php" );
include_once( PATH_LIB . "steam_handling.inc.php" );
include_once( PATH_LIB . "format_handling.inc.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );

$message = steam_factory::get_object( $STEAM->get_id(), $_GET[ "id" ] );

if ( empty( $_GET[ "id" ] )  || !$message )
{
   include( "bad_link.php" );
   exit;
}

$is_sent = ((isset($_GET['sent']) && $_GET['sent'])?TRUE:FALSE);

if ( isset( $_REQUEST[ "reply" ] ) )
{
   header( "Location: " . PATH_URL . "messages_write.php?reply_to=" . $_GET[ "id" ]  );
}
elseif( isset( $_REQUEST[ "delete" ] ) )
{
  $trashbin = $GLOBALS["STEAM"]->get_current_steam_user();
  lms_steam::delete( $message );
  $_SESSION[ "confirmation" ] = gettext( "1 message deleted." );
  header( "Location: " . PATH_URL . "messages.php" );
  exit;
}

$user = lms_steam::get_current_user();

$cache = get_cache_function( $user->get_name(), 600 );
$cache->drop( "lms_steam::user_count_unread_mails", $user->get_name() );

$portal->set_page_title( gettext( "Message" ) );

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "messages_read.template.html" );

$mail_headers = $message->get_attribute( "MAIL_MIMEHEADERS" );
if ( ! is_array( $mail_headers ) )
{
   $mail_headers = array();
}
if ( array_key_exists( "X-Steam-Group", $mail_headers ) )
{
   $groupname = $mail_headers[ "X-Steam-Group" ];
   $content->setCurrentBlock( "BLOCK_GROUP" );
   $content->setVariable( "LABEL_TO", gettext( "To" ) );
   if ( $group = steam_factory::groupname_to_object( $GLOBALS[ "STEAM" ]->get_id(), $groupname ) )
   {
      $content->setVariable( "VALUE_GROUP", "<a href=\"" . PATH_URL . "groups/" . $group->get_id() . "/members/\">" . $group->get_name() . "</a>" );
   }
   else
   {
      $content->setVariable( "VALUE_GROUP", h($groupname) );
   }
   $content->parse( "BLOCK_GROUP" );
}

$message_sender_html = "";
if ($is_sent) {
  $content->setVariable( "LABEL_FROM", gettext( "To" ) );
  // construct the HTML output for the field "receiver"
  $mailto = $message->get_attribute("mailto");
  if (!is_array($mailto)) $mailto = array($mailto);
  $message_sender_html_buffer = "";
  $multiple = FALSE;
  foreach($mailto as $receiver) {
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
            $senderurl = PATH_URL . SEMESTER_URL . "/" . $receiver->get_parent_group()->get_name() .  "/" . $receiver->get_name() . "/";
          } else  if (stristr($grouptype, "group_")) {
            $display_withlink = TRUE;
            $senderurl = PATH_URL . "groups/" . $receiver->get_id() . "/";
          }
        }
      }
      // display using a link for users and koala groups
      $receiver_names = $receiver->get_attributes(array(USER_FIRSTNAME, USER_FULLNAME));
      $receiver_full_name = $receiver_names["USER_FIRSTNAME"] . " " . $receiver_names["USER_FULLNAME"];
      if ($display_withlink) {
        $message_sender_html_buffer = "<a href=\"" . $senderurl . "\"><img src=\"" . PATH_URL . "cached/get_document.php?id=" . $receiver->get_attribute(OBJ_ICON)->get_id() . "&type=usericon&width=30&height=40" . "\" width=\"24\" height=\"28\" align=\"absmiddle\" /></a>&nbsp;<a href=\"" . $senderurl . "\">" . h($receiver_full_name) . "</a>";
      } else  {
          $message_sender_html_buffer = "<img src=\"" . PATH_URL . "cached/get_document.php?id=" . $receiver->get_attribute(OBJ_ICON)->get_id() . "&type=usericon&width=30&height=40" . "\" width=\"24\" height=\"28\" align=\"absmiddle\" />&nbsp;" . h($receiver_full_name);
      }
    } else if (is_string($receiver)) {
      $message_sender_html_buffer = $receiver;
    } else {
      $message_sender_html_buffer = gettext("N.A.");
      error_log("messages.php: found invalid receiver type in 'mailto' of object with id=" . $messages[$i]->get_id());
    }
    $message_sender_html .= ($multiple?", ":"") . $message_sender_html_buffer;
    $multiple = TRUE;
  }
  $content->setVariable( "VALUE_SENDER", $message_sender_html);
} else {
  $content->setVariable( "LABEL_FROM", gettext( "From" ) );
  $sender = $message->get_creator();
  $sender_attributes = $sender->get_attributes( array( "OBJ_NAME", "OBJ_ICON", "USER_FIRSTNAME", "USER_FULLNAME" ) );
  $content->setVariable( "VALUE_SENDER", "<a href=\"" . PATH_URL . "user/" . $sender_attributes[ "OBJ_NAME" ] . "/\">" . "<img src=\"" . PATH_URL . "cached/get_document.php?id=" . $sender_attributes[ "OBJ_ICON" ]->get_id() . "&type=usericon&width=30&height=40" . "\" width=\"24\" height=\"28\" align=\"absmiddle\"></a>&nbsp;<a href=\"" . PATH_URL . "user/" . $sender_attributes[ "OBJ_NAME" ] . "/\">" . $sender_attributes[ "USER_FIRSTNAME" ] . " " . $sender_attributes[ "USER_FULLNAME" ] . "</a>" );
}
$content->setVariable( "LABEL_SUBJECT", gettext( "Subject" ) );
$content->setVariable( "VALUE_SUBJECT", h($message->get_name()) );

// handle multipart messages
$mime = $message->get_attribute(DOC_MIME_TYPE);
$attachments = $message->get_annotations();
$active_message = $message;
// find the textmessage within the attachments
if (stripos($mime, "text/") != -1) {
  $found = FALSE;
  foreach($attachments as $attachment) {
    if (!$found && stripos($mime, "text/") != -1) {
      $active_message = $attachment;
    }
  }
}
$messagebody = $active_message->get_content();
$mime = $active_message->get_attribute(DOC_MIME_TYPE);
if ($mime === "text/plain") {
  $messagebody = nl2br($messagebody);
}
$encoding = $active_message->get_attribute(DOC_ENCODING);
if (strtolower($encoding) === "iso-8859-1") {
  $messagebody = utf8_encode($messagebody);
}
// mark message as read
if ($active_message->get_id() != $message->get_id()) $message->get_content();
$content->setVariable( "LABEL_BODY", gettext( "Message" ) );
$content->setVariable( "VALUE_BODY", $messagebody );
// handle attachments
if ( is_array($attachments) && count($attachments) > 0) {
  $content->setCurrentBlock("BLOCK_ATTACHMENTS");
  foreach($attachments as $attachment) {
    if ($attachment->get_id() != $active_message->get_id()) {
      $content->setCurrentBlock("BLOCK_ATTACHMENT");
      $content->setVariable( "LABEL_ATTACHMENT", gettext( "Attachment" ) );
      $content->setVariable( "VALUE_ATTACHMENT", "<a href=\"" . PATH_URL . "get_document.php?id=" . $attachment->get_id() . "\">" . $active_message->get_name() . "</a>" );
      $content->parse("BLOCK_ATTACHMENT");
    }
  }
  $content->parse("BLOCK_ATTACHMENTS");
}

$content->setVariable( "LABEL_REPLY", gettext( "Send a reply" ) );

$timestamp = $message->get_attribute(OBJ_CREATION_TIME);
$timestamp = strftime( "%x", $timestamp ) . ", " . strftime( "%H:%M", $timestamp);
$content->setVariable( "LABEL_TIMESTAMP", gettext( "Date" ) );
$content->setVariable( "VALUE_TIMESTAMP", $timestamp );

$content->setVariable( "LABEL_WINDOW_CONFIRM", gettext( "Are you sure you want to delete this message?" ) );
$content->setVariable( "MESSAGE_ID", $message->get_id() );

$content->setVariable( "LABEL_DELETE", gettext( "DELETE" ) );

$backlink=PATH_URL . "messages.php" . ($is_sent?"?sent=1":"");

$content->setVariable( "BACKLINK",   $backlink);
if ($is_sent){
  $content->setVariable( "LABEL_RETURN", gettext( "return to your outbox" ) );
  $content->setVariable( "HIDE_REPLY", 'display:none' );
}
else
  $content->setVariable( "LABEL_RETURN", gettext( "return to your inbox" ) );

$portal->set_page_main(array( array( "link" => PATH_URL . "messages.php", "name" => gettext( "mailbox" ) ), array( "link" => $backlink, "name" => (($is_sent)?gettext("Your Sent Mail"):gettext("Your Inbox") )), array( "link" => "", "name" => h($message->get_name()) ) ), $content->get(), "");
$portal->show_html();
?>
