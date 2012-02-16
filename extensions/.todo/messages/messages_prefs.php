<?php
include_once( "../etc/koala.conf.php" );
include_once( PATH_LIB . "steam_handling.inc.php" );
require_once( PATH_LIB . "format_handling.inc.php" );

$portal = lms_portal::get_instance();
$portal->initialize( GUEST_NOT_ALLOWED );
$portal->set_page_title( gettext( "Mail" ) );

$user = lms_steam::get_current_user();
$forwarding = $user->get_email_forwarding();
$user_attributes = $user->get_attributes( array( "USER_FIRSTNAME", "USER_FULLNAME", "USER_EMAIL", "USER_EMAIL_SIGNATURE" ) );
$leave_mails_on_server = in_array( "/" . $user->get_name(), $forwarding );
$forward_mails         = in_array( $user_attributes[ "USER_EMAIL" ], $forwarding );

$content = new HTML_TEMPLATE_IT();
$content->loadTemplateFile( PATH_TEMPLATES . "messages_prefs.template.html" );

if ( $_SERVER[ "REQUEST_METHOD" ] == "POST" )
{
	$values = $_POST[ "values" ];
	
	$problems = "";
	$hints    = "";
	if ( empty( $values[ "email" ] ) && ! empty( $values[ "forward" ] ) )
	{
		$problems = gettext( "The e-mail address is a mandatory field." );
		$hints    = gettext( "Please enter a valid e-mail address of yours." );
	}

	if ( empty( $values[ "forward" ] ) && empty( $values[ "copy" ] ) )
	{
		$problems .= gettext( "If you don't leave the messages on the server, you have to forward them to an external email address. Otherwise, they would not be delivered at all." );
		$content->setVariable( "COPY_CHECKED", 'checked="checked"' );
	}

	if ( empty( $problems ) )
	{
		$confirmation = "";
		$user->set_attribute( "USER_EMAIL_SIGNATURE", $values[ "signature" ] );
		if ( $leave_mails_on_server && empty( $values[ "copy" ] ) )
		{
			$user->delete_forward( "/" . $user->get_name() );
			$leave_mails_on_server = FALSE;
			$confirmation .= gettext( "Mails will not be left on the server any longer." ) . " ";
		}
		elseif( ! $leave_mails_on_server && ! empty( $values[ "copy" ] ) )
		{
			$user->add_forward( "/" . $user->get_name() );
			$leave_mails_on_server = TRUE;
			$confirmation .= gettext( "Mails will be left on this server from now on." ) . " ";
		}
		if ( $forward_mails && empty( $values[ "forward" ] ) )
		{
				$user->delete_forward( $user_attributes[ "USER_EMAIL" ] );
				$user->set_attribute( "USER_FORWARD_MSG", FALSE );
				$forward_mails = FALSE;
				$confirmation .= gettext( "Mails will not be forwarded any longer." ) . " ";
		}
		elseif( ! $forward_mails && ! empty( $values[ "forward" ] ) )
		{
				$user->add_forward( $values[ "email" ] );
				$user->set_attribute( "USER_FORWARD_MSG", TRUE );
				$confirmation .= gettext( "Mails will be forwarded from now on." ) . " ";
		}
		if ( $values[ "email" ] != $user_attributes[ "USER_EMAIL" ] )
		{
			$user->set_attribute( "USER_EMAIL", $values[ "email" ] );
			$confirmation .= gettext( "Email address changed." );
			if ( $forward_mails )
			{
				$user->delete_forward( $user_attributes[ "USER_EMAIL" ] );
				$user->add_forward( $values[ "email" ] );
			}
		}
		$portal->set_confirmation( $confirmation );
	}
	else
	{
		$portal->set_problem_description( $problems, $hints );
	}

	if ( $values[ "forward" ] )
		$content->setVariable( "FORWARD_CHECKED", 'checked="checked"' );
	if ( (isset($values["copy"])) && $values[ "copy" ] )	
		$content->setVariable( "COPY_CHECKED", 'checked="checked"' );
	$content->setVariable( "USER_EMAIL", $values[ "email" ] );
	$content->setVariable( "USER_EMAIL_SIGNATURE", h($values[ "signature" ]) );
}
else
{
	if ( $forward_mails )
		$content->setVariable( "FORWARD_CHECKED", 'checked="checked"' );
	if ( $leave_mails_on_server )
		$content->setVariable( "COPY_CHECKED", 'checked="checked"' );
	$content->setVariable( "USER_EMAIL", $user_attributes[ "USER_EMAIL" ] );
	if ( ! empty( $user_attributes[ "USER_EMAIL_SIGNATURE" ] ) )
		$content->setVariable( "USER_EMAIL_SIGNATURE", $user_attributes[ "USER_EMAIL_SIGNATURE" ] );
}

$content->setVariable( "GREETING", str_replace( "%n", h($portal->get_user()->get_forename()), gettext( "Hi %n!" ) ) );
$content->setVariable( "HELP_TEXT", gettext( "Here you can configure your e-mail preferences for koaLA." ) . "<br/>" . gettext( "Please keep in mind, that reachability is important in some of your courses." ) );
$content->setVariable( "LABEL_CONFIGURE_EMAIL_SETTINGS", gettext( "Configure your E-Mail settings" ) );
$content->setVariable( "LABEL_FORWARD_ADDRESS", gettext( "Your E-Mail-Address" ) );
$content->setVariable( "LABEL_FORWARDING", gettext( "Forwarding" ) );

$content->setVariable( "LABEL_FORWARD_CHECK", gettext( "Forward messages to your e-mail account" ) );
$content->setVariable( "LABEL_COPY_CHECK", gettext( "Leave copy on the server" ) );
$content->setVariable( "LABEL_SIGNATURE", gettext( "Your Signature" ) );
$content->setVariable( "SUBMIT_VALUE", gettext( "Save changes" ) );

$content->setVariable( "CONFIRM_MESSAGE", gettext( "Are you sure about your changes?" ) );
$content->setVariable( "LABEL_RETURN_TO_INBOX", gettext( "return to your inbox" ) );
$content->setVariable( "INBOX_LINK", PATH_URL . "messages.php" );

$portal->set_page_main(
		array( array( "link" => PATH_URL . "messages.php", "name" => gettext("mailbox") ), array( "link" => "", "name" => gettext( "Preferences" ) ) ),
		$content->get(),
		""
		);
$portal->show_html();
?>
